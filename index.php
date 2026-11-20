<?php
require __DIR__.'/config.php';

// Xử lý đăng nhập/đăng xuất trước khi khởi tạo dữ liệu nghiệp vụ.
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $csrf = (string)($_POST['csrf'] ?? '');
    $csrfValid = isset($_SESSION['login_csrf']) && hash_equals($_SESSION['login_csrf'], $csrf);
    if ($csrfValid && hash_equals(ADMIN_USERNAME, $username) && password_verify($password, ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        unset($_SESSION['login_csrf']);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = ADMIN_USERNAME;
        redirect('dashboard', 'Đăng nhập thành công.');
    }
    $loginError = $csrfValid ? 'Tên đăng nhập hoặc mật khẩu không chính xác.' : 'Phiên đăng nhập không hợp lệ. Vui lòng tải lại trang.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    $csrf = (string)($_POST['csrf'] ?? '');
    if (!isset($_SESSION['app_csrf']) || !hash_equals($_SESSION['app_csrf'], $csrf)) {
        http_response_code(403);
        exit('Yêu cầu không hợp lệ. Vui lòng tải lại trang.');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: index.php');
    exit;
}

// Toàn bộ khu vực quản lý chỉ dành cho tài khoản đã đăng nhập.
if (empty($_SESSION['admin_logged_in'])) {
    require __DIR__.'/login.php';
    exit;
}

if (empty($_SESSION['app_csrf'])) $_SESSION['app_csrf'] = bin2hex(random_bytes(32));
$pdo = db();
expireNoShows();
$page = $_GET['page'] ?? 'dashboard';
$allowed = ['dashboard','bookings','booking_new','customers','fields','staff','services','invoices','reports','prices'];
if (!in_array($page, $allowed, true)) $page = 'dashboard';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'customer_add') {
            $name = trim($_POST['ho_ten'] ?? '');
            $phone = trim($_POST['so_dien_thoai'] ?? '');
            if ($name === '' || $phone === '') throw new RuntimeException('Vui lòng nhập họ tên và số điện thoại.');
            $st = $pdo->prepare("INSERT INTO khach_hang(ho_ten,so_dien_thoai,email) VALUES(?,?,?)");
            $st->execute([$name,$phone,trim($_POST['email'] ?? '') ?: null]);
            redirect('customers','Đã thêm khách hàng.');
        }
        if ($action === 'booking_add') {
            $field = (int)$_POST['ma_san'];
            $customer = (int)$_POST['ma_kh'];
            $staff = (int)$_POST['ma_nv_tiep_nhan'];
            $type = $_POST['hinh_thuc_thue'] ?? 'đặt trước';
            if (!staffOnDuty($staff, (new DateTimeImmutable())->format('Y-m-d H:i:s'))) throw new RuntimeException('Nhân viên tiếp nhận không thuộc ca đang làm việc.');
            $hours = (int)$_POST['so_gio'];
            if ($hours < 1 || $hours > 3) throw new RuntimeException('Thời lượng thuê phải từ 1 đến 3 giờ.');
            $start = new DateTimeImmutable($_POST['thoi_gian_bat_dau']);
            if ($start->format('i:s') !== '00:00') throw new RuntimeException('Giờ bắt đầu phải là giờ tròn.');
            if ($start <= new DateTimeImmutable()) throw new RuntimeException('Không được đặt vào thời gian đã qua.');
            if ($type === 'thuê trực tiếp tại quầy') {
                $expected = nextWholeHour(new DateTimeImmutable());
                if ($start->format('Y-m-d H:i') !== $expected->format('Y-m-d H:i')) {
                    throw new RuntimeException('Thuê trực tiếp phải bắt đầu ở giờ tròn kế tiếp.');
                }
            }
            $end = $start->modify("+$hours hour");
            $pdo->beginTransaction();
            $check = $pdo->prepare("SELECT COUNT(*) FROM luot_dat_san
                WHERE ma_san=? AND trang_thai <> 'đã hủy'
                AND thoi_gian_bat_dau < ? AND thoi_gian_ket_thuc > ?");
            $check->execute([$field,$end->format('Y-m-d H:i:s'),$start->format('Y-m-d H:i:s')]);
            if ((int)$check->fetchColumn() > 0) throw new RuntimeException('Sân đã có lượt đặt trùng thời gian.');
            [$total,$slots] = priceForBooking($start,$hours);
            $deposit = $type === 'đặt trước' ? round($total*0.30, 2) : 0;
            $method = $type === 'đặt trước' ? ($_POST['phuong_thuc_coc'] ?? 'tiền mặt') : null;
            $ins = $pdo->prepare("INSERT INTO luot_dat_san
                (ma_san,ma_kh,ma_nv_tiep_nhan,thoi_diem_tao,thoi_gian_bat_dau,thoi_gian_ket_thuc,tong_tien_san,hinh_thuc_thue,trang_thai,tien_coc,phuong_thuc_coc)
                VALUES(?,?,?,NOW(),?,?,?,?, 'đã đặt',?,?)");
            $ins->execute([$field,$customer,$staff,$start->format('Y-m-d H:i:s'),$end->format('Y-m-d H:i:s'),$total,$type,$deposit,$method]);
            $bookingId = (int)$pdo->lastInsertId();
            $slotIns = $pdo->prepare("INSERT INTO chi_tiet_gia_san(ma_dat_san,gio_thue,ma_khung_gio,don_gia_ap_dung) VALUES(?,?,?,?)");
            foreach ($slots as $s) $slotIns->execute([$bookingId,$s['slot'],$s['ma_khung_gio'],$s['price']]);
            $pdo->commit();
            redirect('bookings',"Đã tạo lượt đặt #$bookingId. Tiền sân: ".money($total)."; tiền cọc: ".money($deposit));
        }
        if ($action === 'booking_status') {
            $id=(int)$_POST['id']; $new=$_POST['trang_thai'];
            $q=$pdo->prepare("SELECT * FROM luot_dat_san WHERE ma_dat_san=?"); $q->execute([$id]); $b=$q->fetch();
            if (!$b) throw new RuntimeException('Không tìm thấy lượt đặt.');
            if ($b['trang_thai'] !== 'đã đặt' || !in_array($new,['đang chơi','đã hủy'],true)) throw new RuntimeException('Chuyển trạng thái không hợp lệ.');
            if ($new==='đang chơi') {
                $q=$pdo->prepare("SELECT so_dien_thoai FROM khach_hang WHERE ma_kh=?"); $q->execute([$b['ma_kh']]);
                if (trim($_POST['so_dien_thoai'] ?? '') !== (string)$q->fetchColumn()) throw new RuntimeException('Số điện thoại không khớp với khách đã đặt sân này.');
                $now=new DateTimeImmutable();
                $start=new DateTimeImmutable($b['thoi_gian_bat_dau']);
                if ($now < $start || $now >= $start->modify('+30 minutes')) throw new RuntimeException('Chỉ nhận sân từ giờ bắt đầu đến trước 30 phút sau giờ bắt đầu.');
            }
            $q=$pdo->prepare("UPDATE luot_dat_san SET trang_thai=? WHERE ma_dat_san=?"); $q->execute([$new,$id]);
            redirect('bookings','Đã cập nhật trạng thái lượt đặt.');
        }
        if ($action === 'service_add') {
            $id=(int)$_POST['ma_dat_san']; $service=(int)$_POST['ma_dv']; $qty=max(1,(int)$_POST['so_luong']);
            $pdo->beginTransaction();
            $q=$pdo->prepare("SELECT trang_thai FROM luot_dat_san WHERE ma_dat_san=? FOR UPDATE"); $q->execute([$id]); $b=$q->fetch();
            if (!$b || $b['trang_thai']!=='đang chơi') throw new RuntimeException('Chỉ thêm dịch vụ khi lượt đặt đang chơi.');
            $q=$pdo->prepare("SELECT don_gia FROM dich_vu WHERE ma_dv=?"); $q->execute([$service]); $price=$q->fetchColumn();
            if ($price===false) throw new RuntimeException('Dịch vụ không tồn tại.');
            $q=$pdo->prepare("INSERT INTO chi_tiet_dich_vu(ma_dat_san,ma_dv,so_luong,don_gia,thanh_tien)
                VALUES(?,?,?,?,?) ON DUPLICATE KEY UPDATE so_luong=so_luong+VALUES(so_luong), thanh_tien=so_luong*don_gia");
            $q->execute([$id,$service,$qty,$price,$qty*$price]);
            $pdo->commit();
            redirect('bookings','Đã cập nhật dịch vụ.');
        }
        if ($action === 'pay_invoice') {
            $id=(int)$_POST['ma_dat_san']; $staff=(int)$_POST['ma_nv_lap']; $method=$_POST['phuong_thuc_thanh_toan'] ?? 'tiền mặt';
            $pdo->beginTransaction();
            $q=$pdo->prepare("SELECT l.*, k.tong_diem FROM luot_dat_san l JOIN khach_hang k ON k.ma_kh=l.ma_kh WHERE l.ma_dat_san=? FOR UPDATE");
            $q->execute([$id]); $b=$q->fetch();
            if (!$b || $b['trang_thai']!=='đang chơi') throw new RuntimeException('Chỉ lập hóa đơn cho lượt đang chơi.');
            if (!staffOnDuty($staff, (new DateTimeImmutable())->format('Y-m-d H:i:s'))) throw new RuntimeException('Nhân viên lập hóa đơn không thuộc ca đang làm việc.');
            $q=$pdo->prepare("SELECT COUNT(*) FROM hoa_don WHERE ma_dat_san=?"); $q->execute([$id]);
            if ((int)$q->fetchColumn()) throw new RuntimeException('Lượt đặt đã có hóa đơn.');
            $q=$pdo->prepare("SELECT COALESCE(SUM(thanh_tien),0) FROM chi_tiet_dich_vu WHERE ma_dat_san=?"); $q->execute([$id]); $services=(float)$q->fetchColumn();
            [$tier,$rate]=currentTier((int)$b['tong_diem']);
            $discount=round((float)$b['tong_tien_san']*$rate,2);
            $deposit=(float)$b['tien_coc'];
            $due=(float)$b['tong_tien_san']-$discount-$deposit+$services;
            $q=$pdo->prepare("INSERT INTO hoa_don(ma_dat_san,ma_nv_lap,thoi_diem_lap,phuong_thuc_thanh_toan,tien_san,tien_giam_gia,tien_coc_da_tru,tien_dich_vu,so_tien_phai_tra)
                VALUES(?,?,NOW(),?,?,?,?,?,?)");
            $q->execute([$id,$staff,$method,$b['tong_tien_san'],$discount,$deposit,$services,$due]);
            $points=(int)floor($due/100000);
            $newPoints=(int)$b['tong_diem']+$points;
            [$newTier]=currentTier($newPoints);
            $q=$pdo->prepare("UPDATE khach_hang SET tong_diem=?,cap_bac=? WHERE ma_kh=?"); $q->execute([$newPoints,$newTier,$b['ma_kh']]);
            $q=$pdo->prepare("UPDATE luot_dat_san SET trang_thai='đã hoàn thành' WHERE ma_dat_san=?"); $q->execute([$id]);
            $pdo->commit();
            redirect('invoices',"Đã thanh toán hóa đơn. Khách được cộng $points điểm; tổng điểm mới: $newPoints ($newTier).");
        }
        if ($action === 'field_add') {
            $pdo->prepare("INSERT INTO san_bong(tinh_trang) VALUES('trống')")->execute();
            redirect('fields','Đã thêm sân bóng.');
        }
        if ($action === 'service_create') {
            $pdo->prepare("INSERT INTO dich_vu(ten_dv,don_gia) VALUES(?,?)")->execute([trim($_POST['ten_dv']), (float)$_POST['don_gia']]);
            redirect('services','Đã thêm dịch vụ.');
        }
        if ($action === 'staff_create') {
            $pdo->prepare("INSERT INTO nhan_vien(ho_ten,so_dien_thoai,ngay_sinh,ma_ca) VALUES(?,?,?,?)")
                ->execute([trim($_POST['ho_ten']),trim($_POST['so_dien_thoai']),$_POST['ngay_sinh'] ?: null,(int)$_POST['ma_ca']]);
            redirect('staff','Đã thêm nhân viên.');
        }
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $ex->getMessage();
    }
}

$stats = [];
$stats['fields']=(int)$pdo->query("SELECT COUNT(*) FROM san_bong")->fetchColumn();
$stats['playing']=(int)$pdo->query("SELECT COUNT(*) FROM luot_dat_san WHERE trang_thai='đang chơi'")->fetchColumn();
$stats['booked']=(int)$pdo->query("SELECT COUNT(*) FROM luot_dat_san WHERE trang_thai='đã đặt'")->fetchColumn();
$stats['customers']=(int)$pdo->query("SELECT COUNT(*) FROM khach_hang")->fetchColumn();
$stats['revenue']=(float)$pdo->query("SELECT (SELECT COALESCE(SUM(so_tien_phai_tra),0) FROM hoa_don WHERE DATE(thoi_diem_lap)=CURDATE()) + (SELECT COALESCE(SUM(tien_coc),0) FROM luot_dat_san WHERE DATE(thoi_diem_tao)=CURDATE())")->fetchColumn();
$stats['available']=$stats['fields']-$stats['playing'];


// Dữ liệu phục vụ form tạo lượt đặt: chỉ hiển thị nhân viên đang trong ca,
// và sân không bị trùng lịch ở khoảng thời gian đang chọn.
$bookingForm = $_POST;
$bookingStartValue = $bookingForm['thoi_gian_bat_dau'] ?? nextWholeHour(new DateTimeImmutable())->format('Y-m-d\TH:i');
$bookingHoursValue = (int)($bookingForm['so_gio'] ?? 2);
if ($bookingHoursValue < 1 || $bookingHoursValue > 3) $bookingHoursValue = 2;
try {
    $availabilityStart = new DateTimeImmutable($bookingStartValue);
} catch (Throwable $e) {
    $availabilityStart = nextWholeHour(new DateTimeImmutable());
    $bookingStartValue = $availabilityStart->format('Y-m-d\TH:i');
}
$availabilityEnd = $availabilityStart->modify("+$bookingHoursValue hour");
$allFields = $pdo->query("SELECT ma_san FROM san_bong ORDER BY ma_san")->fetchAll();
$busyBookings = $pdo->query("SELECT ma_san,thoi_gian_bat_dau,thoi_gian_ket_thuc FROM luot_dat_san WHERE trang_thai <> 'đã hủy'")->fetchAll();
$busyFieldIds = [];
foreach ($busyBookings as $busy) {
    if (new DateTimeImmutable($busy['thoi_gian_bat_dau']) < $availabilityEnd &&
        new DateTimeImmutable($busy['thoi_gian_ket_thuc']) > $availabilityStart) {
        $busyFieldIds[] = (int)$busy['ma_san'];
    }
}
$availableFields = array_values(array_filter($allFields, fn($f) => !in_array((int)$f['ma_san'], $busyFieldIds, true)));
$onDutyStaff = [];
$nowForShift = (new DateTimeImmutable())->format('Y-m-d H:i:s');
foreach ($pdo->query("SELECT ma_nv,ho_ten FROM nhan_vien ORDER BY ho_ten")->fetchAll() as $staffRow) {
    if (staffOnDuty((int)$staffRow['ma_nv'], $nowForShift)) $onDutyStaff[] = $staffRow;
}

include __DIR__.'/views.php';
