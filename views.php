<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>FootballField | Quản lý sân bóng</title>
<link rel="stylesheet" href="assets/style.css?v=2">
</head>
<body>
<div class="layout">
<aside class="sidebar">
  <div class="brand"><div class="brand-icon">⚽</div><div><b>FootballField</b><small>FIELD MANAGEMENT</small></div></div>
  <div class="nav-label">QUẢN LÝ</div>
  <?php
  $nav=['dashboard'=>'Tổng quan','bookings'=>'Lượt đặt sân','booking_new'=>'Tạo lượt đặt','fields'=>'Sân bóng','customers'=>'Khách hàng','staff'=>'Nhân viên & ca','services'=>'Dịch vụ','prices'=>'Bảng giá','invoices'=>'Hóa đơn','reports'=>'Thống kê'];
  $icons=['dashboard'=>'▦','bookings'=>'▤','booking_new'=>'＋','fields'=>'▥','customers'=>'♙','staff'=>'♧','services'=>'◇','prices'=>'₫','invoices'=>'▧','reports'=>'▤'];
  foreach($nav as $k=>$v): ?>
    <a class="nav <?= $page===$k?'active':'' ?>" href="index.php?page=<?=e($k)?>"><span><?= $icons[$k] ?></span><?=e($v)?></a>
  <?php endforeach; ?>
  <div class="sidebar-bottom"><span class="online"></span> Hệ thống đang hoạt động<small>Demo BTL CSDL · Nhóm 20</small></div>
</aside>
<main class="main">
<header class="topbar"><div><span class="crumb">FootballField</span><span class="slash"> / </span><?=e($nav[$page] ?? 'Tổng quan')?></div><div class="topbar-actions"><div class="user"><div class="avatar">AD</div><div><b>Quản trị viên</b><small><?=e($_SESSION['admin_username'] ?? 'admin')?></small></div></div><form method="post" class="logout-form" onsubmit="return confirm('Bạn có chắc muốn đăng xuất?')"><input type="hidden" name="action" value="logout"><input type="hidden" name="csrf" value="<?=e($_SESSION['app_csrf'])?>"><button class="btn logout-btn" type="submit">Đăng xuất</button></form></div></header>
<section class="content">
<?php if ($m=flash()): ?><div class="alert success"><?=e($m)?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?=e($error)?></div><?php endif; ?>

<?php if($page==='dashboard'): ?>
  <div class="page-head"><div><div class="eyebrow">TỔNG QUAN HỆ THỐNG</div><h1>Xin chào, quản lý! 👋</h1><p>Theo dõi hoạt động cho thuê sân bóng hôm nay.</p></div><a class="btn primary" href="index.php?page=booking_new">＋ Tạo lượt đặt</a></div>
  <div class="stats">
    <div class="stat"><span class="stat-icon green">▥</span><small>Tổng số sân</small><strong><?=$stats['fields']?></strong><em> sân bóng</em></div>
    <div class="stat"><span class="stat-icon blue">◷</span><small>Sân đang chơi</small><strong><?=$stats['playing']?></strong><em> lượt đang diễn ra</em></div>
    <div class="stat"><span class="stat-icon amber">▤</span><small>Lượt đã đặt</small><strong><?=$stats['booked']?></strong><em> lượt chờ nhận sân</em></div>
    <div class="stat"><span class="stat-icon violet">♙</span><small>Khách hàng</small><strong><?=$stats['customers']?></strong><em> hồ sơ</em></div>
  </div>
  <div class="grid two">
    <div class="panel"><div class="panel-title"><div><h3>Doanh thu hôm nay</h3><p>Tổng số tiền khách phải trả trên hóa đơn</p></div><span class="pill green-pill">Hôm nay</span></div><div class="revenue"><?=money($stats['revenue'])?></div><div class="muted">Doanh thu gồm tiền khách trả trên hóa đơn và tiền cọc thu trong ngày.</div></div>
    <div class="panel dark-panel"><div class="panel-title"><div><h3>Trạng thái sân</h3><p>Tổng quan theo trạng thái lượt chơi hiện tại</p></div><span>⚽</span></div><div class="availability"><div><strong><?=$stats['available']?></strong><small>Sân chưa chơi*</small></div><div><strong><?=$stats['playing']?></strong><small>Đang chơi</small></div></div><small class="dark-note">*Số sân chưa có lượt đang chơi, không thay thế kiểm tra lịch trống theo khoảng giờ.</small></div>
  </div>
  <div class="panel"><div class="panel-title"><div><h3>Lượt đặt gần đây</h3><p>Các lượt đặt mới nhất trong hệ thống</p></div><a class="text-link" href="index.php?page=bookings">Xem tất cả →</a></div><?php $rows=$pdo->query("SELECT l.*,s.ma_san,k.ho_ten FROM luot_dat_san l JOIN san_bong s ON s.ma_san=l.ma_san JOIN khach_hang k ON k.ma_kh=l.ma_kh ORDER BY l.ma_dat_san DESC LIMIT 6")->fetchAll(); ?>
  <?php if(!$rows): ?><div class="empty">Chưa có lượt đặt. Hãy tạo lượt đặt đầu tiên để bắt đầu demo.</div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Mã đặt</th><th>Khách hàng</th><th>Sân</th><th>Thời gian</th><th>Tiền sân</th><th>Trạng thái</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td>#<?=$r['ma_dat_san']?></td><td><?=e($r['ho_ten'])?></td><td>Sân <?=$r['ma_san']?></td><td><?=date('d/m H:i',strtotime($r['thoi_gian_bat_dau']))?></td><td><?=money($r['tong_tien_san'])?></td><td><span class="status"><?=e($r['trang_thai'])?></span></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>

<?php elseif($page==='booking_new'): ?>
  <div class="page-head"><div><div class="eyebrow">ĐẶT SÂN</div><h1>Tạo lượt đặt sân</h1><p>Chỉ nhân viên đang trong ca và sân còn trống ở khung giờ đã chọn mới được hiển thị.</p></div></div>
  <div class="panel form-panel"><form method="post" class="form-grid" id="bookingForm"><input type="hidden" name="action" value="booking_add">
    <label>Khách hàng<select name="ma_kh" required>
      <?php $selectedCustomer=(string)($bookingForm['ma_kh']??''); foreach($pdo->query("SELECT * FROM khach_hang ORDER BY ho_ten") as $c): ?>
      <option value="<?=$c['ma_kh']?>" <?=$selectedCustomer===(string)$c['ma_kh']?'selected':''?>><?=e($c['ho_ten'])?> · <?=e($c['so_dien_thoai'])?></option><?php endforeach; ?>
    </select></label>
    <label>Sân bóng<select name="ma_san" id="bookingField" required>
      <?php $selectedField=(string)($bookingForm['ma_san']??''); foreach($allFields as $s):
        $isBusy=in_array((int)$s['ma_san'],$busyFieldIds,true); ?>
        <option value="<?=$s['ma_san']?>" data-busy="<?=$isBusy?'1':'0'?>" <?=$selectedField===(string)$s['ma_san']?'selected':''?> <?=($isBusy && $selectedField!==(string)$s['ma_san'])?'hidden disabled':''?>>
          Sân <?=$s['ma_san']?><?=$isBusy?' · Đã có lịch trong khung giờ':''?>
        </option>
      <?php endforeach; ?>
    </select><small class="muted" id="fieldAvailabilityHint">Danh sách sân tự cập nhật theo thời gian và số giờ thuê.</small></label>
    <label>Nhân viên tiếp nhận<select name="ma_nv_tiep_nhan" required>
      <?php $selectedStaff=(string)($bookingForm['ma_nv_tiep_nhan']??''); foreach($onDutyStaff as $n): ?>
      <option value="<?=$n['ma_nv']?>" <?=$selectedStaff===(string)$n['ma_nv']?'selected':''?>><?=e($n['ho_ten'])?></option><?php endforeach; ?>
    </select><?php if(!$onDutyStaff): ?><small class="muted">Hiện không có nhân viên nào đang trong ca làm việc.</small><?php endif; ?></label>
    <label>Hình thức thuê<select name="hinh_thuc_thue" id="bookingType">
      <option value="đặt trước" <?=($bookingForm['hinh_thuc_thue']??'đặt trước')==='đặt trước'?'selected':''?>>Đặt trước</option>
      <option value="thuê trực tiếp tại quầy" <?=($bookingForm['hinh_thuc_thue']??'')==='thuê trực tiếp tại quầy'?'selected':''?>>Thuê trực tiếp tại quầy</option>
    </select></label>
    <label>Thời điểm bắt đầu<input type="datetime-local" name="thoi_gian_bat_dau" id="bookingStart" value="<?=e($bookingStartValue)?>" required></label>
    <label>Số giờ thuê<select name="so_gio" id="bookingHours">
      <?php foreach([1,2,3] as $h): ?><option value="<?=$h?>" <?=$bookingHoursValue===$h?'selected':''?>><?=$h?> giờ</option><?php endforeach; ?>
    </select></label>
    <label id="depositMethod">Phương thức thanh toán cọc<select name="phuong_thuc_coc">
      <?php $depositValue=$bookingForm['phuong_thuc_coc']??'tiền mặt'; foreach(['tiền mặt','chuyển khoản','quẹt thẻ'] as $m): ?><option value="<?=e($m)?>" <?=$depositValue===$m?'selected':''?>><?=e(ucfirst($m))?></option><?php endforeach; ?>
    </select></label>
    <div class="form-note">Quy tắc: thời gian thuê 1–3 giờ, giờ bắt đầu tròn; đặt trước cọc 30%; thuê trực tiếp bắt đầu đúng giờ tròn kế tiếp và không thu cọc.</div>
    <div class="form-actions"><button class="btn primary" <?=$onDutyStaff?'':'disabled'?>>Tạo lượt đặt</button><a class="btn secondary" href="index.php?page=bookings">Hủy</a></div>
  </form></div>
  <script>
  window.bookingAvailability = {
    bookings: <?=json_encode($busyBookings, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>,
    fields: <?=json_encode(array_map(fn($f)=>(int)$f['ma_san'],$allFields))?>
  };
  </script>

<?php elseif($page==='bookings'): ?>
  <div class="page-head"><div><div class="eyebrow">VẬN HÀNH</div><h1>Quản lý lượt đặt sân</h1><p>Theo dõi trạng thái đặt, nhận sân, dịch vụ và thanh toán.</p></div><a class="btn primary" href="index.php?page=booking_new">＋ Tạo lượt đặt</a></div>
  <?php $phoneQ=trim($_GET['phone']??''); $st=$pdo->prepare("SELECT l.*,s.ma_san,k.ho_ten,k.so_dien_thoai FROM luot_dat_san l JOIN san_bong s ON s.ma_san=l.ma_san JOIN khach_hang k ON k.ma_kh=l.ma_kh WHERE (?='' OR k.so_dien_thoai=?) ORDER BY l.ma_dat_san DESC"); $st->execute([$phoneQ,$phoneQ]); $rows=$st->fetchAll(); ?>
  <div class="panel"><form method="get" class="inline-form"><input type="hidden" name="page" value="bookings"><label>Tra cứu theo số điện thoại khách<input name="phone" value="<?=e($phoneQ)?>" placeholder="VD: 0912000001" pattern="0[0-9]{9}" maxlength="10"></label><button class="btn primary">Tìm lượt đặt</button><?php if($phoneQ!==''): ?><a class="btn secondary" href="index.php?page=bookings">Xem tất cả</a><?php endif; ?></form></div>
  <div class="panel"><div class="table-wrap"><table><thead><tr><th>Mã</th><th>Khách hàng</th><th>Sân</th><th>Bắt đầu – kết thúc</th><th>Tiền sân / cọc</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td>#<?=$r['ma_dat_san']?></td><td><?=e($r['ho_ten'])?><br><small><?=e($r['so_dien_thoai'])?></small></td><td>Sân <?=$r['ma_san']?></td><td><div class="booking-time"><div class="booking-time-hours"><?=date('H:i',strtotime($r['thoi_gian_bat_dau']))?> – <?=date('H:i',strtotime($r['thoi_gian_ket_thuc']))?></div><div class="booking-time-dates"><?=date('d/m/Y',strtotime($r['thoi_gian_bat_dau']))?> – <?=date('d/m/Y',strtotime($r['thoi_gian_ket_thuc']))?></div></div></td><td><?=money($r['tong_tien_san'])?><br><small>Cọc: <?=money($r['tien_coc'])?></small></td><td><span class="status"><?=e($r['trang_thai'])?></span></td><td class="actions">
  <?php if($r['trang_thai']==='đã đặt'): ?><form method="post"><input type="hidden" name="action" value="booking_status"><input type="hidden" name="id" value="<?=$r['ma_dat_san']?>"><input type="hidden" name="trang_thai" value="đang chơi"><input name="so_dien_thoai" placeholder="SĐT khách" required pattern="0[0-9]{9}" maxlength="10" style="width:110px"><button class="mini green-btn">Nhận sân</button></form><form method="post" onsubmit="return confirm('Hủy lượt đặt này? Tiền cọc không hoàn lại.')"><input type="hidden" name="action" value="booking_status"><input type="hidden" name="id" value="<?=$r['ma_dat_san']?>"><input type="hidden" name="trang_thai" value="đã hủy"><button class="mini red-btn">Hủy</button></form>
  <?php elseif($r['trang_thai']==='đang chơi'): ?><a class="mini blue-btn" href="index.php?page=services&booking=<?=$r['ma_dat_san']?>">Dịch vụ</a><a class="mini green-btn" href="index.php?page=invoices&booking=<?=$r['ma_dat_san']?>">Thanh toán</a><?php else: ?>—<?php endif; ?>
  </td></tr><?php endforeach; ?></tbody></table></div><?php if(!$rows): ?><div class="empty">Chưa có lượt đặt sân.</div><?php endif; ?></div>

<?php elseif($page==='customers'): ?>
  <div class="page-head"><div><div class="eyebrow">DỮ LIỆU KHÁCH HÀNG</div><h1>Khách hàng</h1><p>Mỗi khách hàng có một hồ sơ duy nhất theo số điện thoại.</p></div></div>
  <div class="grid two align-start"><div class="panel"><h3>Thêm khách hàng</h3><form method="post" class="stack-form"><input type="hidden" name="action" value="customer_add"><label>Họ và tên<input name="ho_ten" required></label><label>Số điện thoại<input name="so_dien_thoai" required pattern="0[0-9]{9}" maxlength="10" title="10 chữ số, bắt đầu bằng 0"></label><label>Email<input type="email" name="email"></label><button class="btn primary">Lưu khách hàng</button></form></div>
  <div class="panel"><h3>Quy tắc thành viên</h3><div class="tier-row"><b>Bronze</b><span>0–49 điểm · Không giảm</span></div><div class="tier-row"><b>Silver</b><span>50–149 điểm · Giảm 5% tiền sân</span></div><div class="tier-row"><b>Gold</b><span>Từ 150 điểm · Giảm 10% tiền sân</span></div><p class="muted">Mỗi 100.000đ khách phải trả quy đổi 1 điểm, làm tròn xuống.</p></div></div>
  <?php $rows=$pdo->query("SELECT * FROM khach_hang ORDER BY tong_diem DESC")->fetchAll(); ?><div class="panel"><div class="table-wrap"><table><thead><tr><th>Mã KH</th><th>Họ tên</th><th>Số điện thoại</th><th>Email</th><th>Điểm</th><th>Cấp bậc</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td>#<?=$r['ma_kh']?></td><td><?=e($r['ho_ten'])?></td><td><?=e($r['so_dien_thoai'])?></td><td><?=e($r['email'])?></td><td><b><?=$r['tong_diem']?></b></td><td><span class="status"><?=e($r['cap_bac'])?></span></td></tr><?php endforeach; ?></tbody></table></div></div>

<?php elseif($page==='fields'): ?>
  <div class="page-head"><div><div class="eyebrow">CƠ SỞ VẬT CHẤT</div><h1>Danh sách sân bóng</h1><p>Tình trạng đang chơi được suy ra từ các lượt đặt có trạng thái đang chơi.</p></div><form method="post"><input type="hidden" name="action" value="field_add"><button class="btn primary">＋ Thêm sân</button></form></div>
  <div class="field-grid"><?php foreach($pdo->query("SELECT s.*, EXISTS(SELECT 1 FROM luot_dat_san l WHERE l.ma_san=s.ma_san AND l.trang_thai='đang chơi') AS playing FROM san_bong s ORDER BY ma_san") as $s): ?><div class="field-card"><div class="field-visual">⚽<span>SÂN 7 NGƯỜI</span></div><div class="field-info"><b>Sân số <?=$s['ma_san']?></b><span class="status"><?=$s['playing']?'đang cho thuê':'trống'?></span></div></div><?php endforeach; ?></div>

<?php elseif($page==='staff'): ?>
  <div class="page-head"><div><div class="eyebrow">NHÂN SỰ</div><h1>Nhân viên & ca làm</h1><p>Mỗi nhân viên thuộc đúng một ca cố định.</p></div></div>
  <div class="grid two align-start"><div class="panel"><h3>Thêm nhân viên</h3><form method="post" class="stack-form"><input type="hidden" name="action" value="staff_create"><label>Họ tên<input name="ho_ten" required></label><label>Số điện thoại<input name="so_dien_thoai" required pattern="0[0-9]{9}" maxlength="10" title="10 chữ số, bắt đầu bằng 0"></label><label>Ngày sinh<input type="date" name="ngay_sinh" required></label><label>Ca làm<select name="ma_ca"><?php foreach($pdo->query("SELECT * FROM ca_lam") as $c): ?><option value="<?=$c['ma_ca']?>"><?=e($c['ten_ca'])?></option><?php endforeach; ?></select></label><button class="btn primary">Thêm nhân viên</button></form></div><div class="panel"><h3>Danh sách nhân viên</h3><?php foreach($pdo->query("SELECT n.*,c.ten_ca FROM nhan_vien n JOIN ca_lam c ON c.ma_ca=n.ma_ca") as $n): ?><div class="list-row"><b><?=e($n['ho_ten'])?></b><span><?=e($n['ten_ca'])?></span></div><?php endforeach; ?><h3 class="mt">Ca làm việc</h3><p>Ca ngày: 06:00–18:00</p><p>Ca đêm: 18:00–06:00 hôm sau</p></div></div>

<?php elseif($page==='services'): ?>
  <div class="page-head"><div><div class="eyebrow">DỊCH VỤ PHÁT SINH THEO SÂN</div><h1>Gọi dịch vụ</h1><p>Chọn lượt đang chơi của từng sân. Món gọi sẽ được lưu riêng cho lượt đó và cộng vào hóa đơn cuối buổi.</p></div></div>
  <?php $activeBookings=$pdo->query("SELECT l.ma_dat_san,l.ma_san,l.thoi_gian_bat_dau,k.ho_ten,COALESCE(SUM(c.thanh_tien),0) AS tong_dich_vu FROM luot_dat_san l JOIN khach_hang k ON k.ma_kh=l.ma_kh LEFT JOIN chi_tiet_dich_vu c ON c.ma_dat_san=l.ma_dat_san WHERE l.trang_thai='đang chơi' GROUP BY l.ma_dat_san ORDER BY l.ma_san")->fetchAll(); ?>
  <div class="panel"><h3>Các sân đang sử dụng</h3><?php if(!$activeBookings): ?><div class="empty">Hiện chưa có sân nào đang chơi. Hãy nhận sân trong mục Lượt đặt sân trước.</div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Sân</th><th>Mã lượt</th><th>Khách hàng</th><th>Bắt đầu</th><th>Tổng dịch vụ đã gọi</th><th></th></tr></thead><tbody><?php foreach($activeBookings as $ab): ?><tr><td><b>Sân <?=$ab['ma_san']?></b></td><td>#<?=$ab['ma_dat_san']?></td><td><?=e($ab['ho_ten'])?></td><td><?=date('H:i d/m',strtotime($ab['thoi_gian_bat_dau']))?></td><td><b><?=money($ab['tong_dich_vu'])?></b></td><td><a class="mini blue-btn" href="index.php?page=services&booking=<?=$ab['ma_dat_san']?>">Gọi / xem dịch vụ</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>
  <?php if(isset($_GET['booking'])): $bid=(int)$_GET['booking']; $q=$pdo->prepare("SELECT l.*,s.ma_san,k.ho_ten FROM luot_dat_san l JOIN san_bong s ON s.ma_san=l.ma_san JOIN khach_hang k ON k.ma_kh=l.ma_kh WHERE l.ma_dat_san=? AND l.trang_thai='đang chơi'");$q->execute([$bid]);$selectedBooking=$q->fetch(); if($selectedBooking): ?>
  <div class="panel"><h3>Gọi dịch vụ — Sân <?=$selectedBooking['ma_san']?> · Lượt #<?=$bid?></h3><p class="muted">Khách hàng: <b><?=e($selectedBooking['ho_ten'])?></b>. Chọn món và số lượng, sau đó bấm Gọi dịch vụ.</p><form method="post" class="inline-form"><input type="hidden" name="action" value="service_add"><input type="hidden" name="ma_dat_san" value="<?=$bid?>"><select name="ma_dv" required><?php foreach($pdo->query("SELECT * FROM dich_vu ORDER BY ten_dv") as $d): ?><option value="<?=$d['ma_dv']?>"><?=e($d['ten_dv'])?> · <?=money($d['don_gia'])?></option><?php endforeach; ?></select><input type="number" name="so_luong" min="1" value="1" required><button class="btn primary">＋ Gọi dịch vụ</button></form></div>
  <div class="panel"><h3>Thống kê dịch vụ của Sân <?=$selectedBooking['ma_san']?></h3><?php $q=$pdo->prepare("SELECT c.*,d.ten_dv FROM chi_tiet_dich_vu c JOIN dich_vu d ON d.ma_dv=c.ma_dv WHERE c.ma_dat_san=? ORDER BY d.ten_dv");$q->execute([$bid]);$called=$q->fetchAll();$serviceTotal=0; ?><div class="table-wrap"><table><thead><tr><th>Tên dịch vụ</th><th>Số lượng đã gọi</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody><?php foreach($called as $r): $serviceTotal+=(float)$r['thanh_tien']; ?><tr><td><?=e($r['ten_dv'])?></td><td><?=$r['so_luong']?></td><td><?=money($r['don_gia'])?></td><td><?=money($r['thanh_tien'])?></td></tr><?php endforeach; ?></tbody></table></div><?php if(!$called): ?><div class="empty">Lượt này chưa gọi dịch vụ nào.</div><?php endif; ?><div class="bill-total"><span>Tổng tiền dịch vụ tạm tính</span><strong><?=money($serviceTotal)?></strong></div><a class="btn green-btn" href="index.php?page=invoices&booking=<?=$bid?>">Xem tổng kết hóa đơn cuối buổi →</a></div>
  <?php elseif(isset($_GET['booking'])): ?><div class="alert error">Không tìm thấy lượt đang chơi này. Hãy chọn lại sân ở danh sách phía trên.</div><?php endif; ?><?php endif; ?>
  <div class="panel"><h3>Danh mục món / dịch vụ có sẵn</h3><p class="muted">Đây là danh mục để nhân viên chọn khi khách gọi món; không phải danh sách dịch vụ đã gọi của từng sân.</p><?php foreach($pdo->query("SELECT * FROM dich_vu ORDER BY ten_dv") as $d): ?><div class="list-row"><b><?=e($d['ten_dv'])?></b><span><?=money($d['don_gia'])?></span></div><?php endforeach; ?></div>
  <div class="panel"><h3>Quản lý danh mục dịch vụ</h3><form method="post" class="inline-form"><input type="hidden" name="action" value="service_create"><input name="ten_dv" placeholder="Tên món / dịch vụ mới" required><input type="number" name="don_gia" min="0" placeholder="Đơn giá" required><button class="btn secondary">Thêm vào danh mục</button></form></div>

<?php elseif($page==='invoices'): ?>
  <div class="page-head"><div><div class="eyebrow">THANH TOÁN</div><h1>Hóa đơn</h1><p>Lập hóa đơn cho lượt đang chơi; sau thanh toán lượt chuyển thành đã hoàn thành.</p></div></div>
  <?php if(isset($_GET['booking'])): $bid=(int)$_GET['booking']; $q=$pdo->prepare("SELECT l.*,k.ho_ten,k.tong_diem,k.cap_bac FROM luot_dat_san l JOIN khach_hang k ON k.ma_kh=l.ma_kh WHERE ma_dat_san=?");$q->execute([$bid]);$b=$q->fetch(); if($b): [$tier,$rate]=currentTier((int)$b['tong_diem']);$q=$pdo->prepare("SELECT COALESCE(SUM(thanh_tien),0) FROM chi_tiet_dich_vu WHERE ma_dat_san=?");$q->execute([$bid]);$svc=(float)$q->fetchColumn();$discount=$b['tong_tien_san']*$rate;$due=$b['tong_tien_san']-$discount-$b['tien_coc']+$svc; ?>
  <div class="panel invoice-preview"><div class="invoice-brand">⚽ FootballField</div><h2>Hóa đơn tạm tính #<?=str_pad((string)$bid,5,'0',STR_PAD_LEFT)?></h2><p>Khách hàng: <b><?=e($b['ho_ten'])?></b> · <?=e($tier)?></p><div class="bill-line"><span>Tiền sân</span><b><?=money($b['tong_tien_san'])?></b></div><div class="bill-line"><span>Giảm giá (<?=round($rate*100)?>%)</span><b>- <?=money($discount)?></b></div><div class="bill-line"><span>Tiền cọc đã trừ</span><b>- <?=money($b['tien_coc'])?></b></div><div class="bill-line"><span>Dịch vụ</span><b>+ <?=money($svc)?></b></div><?php $q=$pdo->prepare("SELECT d.ten_dv,c.so_luong,c.don_gia,c.thanh_tien FROM chi_tiet_dich_vu c JOIN dich_vu d ON d.ma_dv=c.ma_dv WHERE c.ma_dat_san=? ORDER BY d.ten_dv");$q->execute([$bid]);$invoiceServices=$q->fetchAll(); if($invoiceServices): ?><div class="invoice-service-details"><b>Chi tiết dịch vụ đã sử dụng</b><?php foreach($invoiceServices as $is): ?><div class="bill-line"><span><?=e($is['ten_dv'])?> × <?=$is['so_luong']?> <small>(<?=money($is['don_gia'])?>/món)</small></span><b><?=money($is['thanh_tien'])?></b></div><?php endforeach; ?></div><?php endif; ?><div class="bill-total"><span>Khách phải trả</span><strong><?=money($due)?></strong></div>
  <form method="post" class="inline-form" id="invoicePaymentForm"><input type="hidden" name="action" value="pay_invoice"><input type="hidden" name="ma_dat_san" value="<?=$bid?>"><label>Nhân viên lập<select name="ma_nv_lap" required <?=empty($onDutyStaff)?'disabled':''?>><option value="">-- Chọn nhân viên đang trong ca --</option><?php foreach($onDutyStaff as $n): ?><option value="<?=$n['ma_nv']?>"><?=e($n['ho_ten'])?></option><?php endforeach; ?></select><?php if(empty($onDutyStaff)): ?><small class="muted">Hiện không có nhân viên nào đang trong ca làm việc.</small><?php else: ?><small class="muted">Chỉ hiển thị nhân viên đang trong ca tại thời điểm thanh toán.</small><?php endif; ?></label><label>Thanh toán<select name="phuong_thuc_thanh_toan" id="paymentMethod"><option>tiền mặt</option><option>chuyển khoản</option><option>quẹt thẻ</option></select></label><button class="btn primary" <?=$b['trang_thai']!=='đang chơi'?'disabled':''?>>Xác nhận thanh toán</button></form><small class="muted">Điểm dự kiến: <?=floor($due/100000)?> điểm. Cấp bậc được tính lại sau khi thanh toán.</small></div>
  <div class="qr-modal" id="demoQrModal" aria-hidden="true"><div class="qr-dialog" role="dialog" aria-modal="true" aria-labelledby="qrTitle"><button type="button" class="qr-close" id="closeQr" aria-label="Đóng">×</button><div class="qr-kicker">FOOTBALLFIELD · DEMO PAYMENT</div><h2 id="qrTitle">Quét mã QR để chuyển khoản</h2><p class="qr-subtitle">Mã QR minh họa — không dùng để thanh toán thật</p><img class="demo-qr" src="assets/demo-qr.png" alt="Mã QR minh họa không có thông tin thanh toán thật"><div class="qr-amount"><span>Số tiền cần thanh toán</span><strong><?=money($due)?></strong></div><div class="qr-bank-details"><div><span>Ngân hàng</span><b>Ngân hàng Demo</b></div><div><span>Số tài khoản</span><b>0000 1234 5678</b></div><div><span>Chủ tài khoản</span><b>FOOTBALLFIELD DEMO</b></div><div><span>Nội dung</span><b>FF THANH TOAN HD <?=$bid?></b></div></div><div class="qr-warning">Đây chỉ là màn hình mô phỏng giao diện chuyển khoản. Không phát sinh giao dịch hoặc xác nhận tiền thật.</div><button type="button" class="btn primary qr-done" id="closeQrDone">Đã xem QR — quay lại hóa đơn</button></div></div>
  <?php endif; endif; ?>
  <?php $rows=$pdo->query("SELECT h.*,l.ma_dat_san,k.ho_ten,n.ho_ten AS nhan_vien FROM hoa_don h JOIN luot_dat_san l ON l.ma_dat_san=h.ma_dat_san JOIN khach_hang k ON k.ma_kh=l.ma_kh JOIN nhan_vien n ON n.ma_nv=h.ma_nv_lap ORDER BY h.ma_hd DESC")->fetchAll(); ?><div class="panel"><h3>Lịch sử hóa đơn</h3><div class="table-wrap"><table><tr><th>Mã HĐ</th><th>Lượt đặt</th><th>Khách hàng</th><th>Thời điểm</th><th>Phương thức</th><th>Phải trả</th></tr><?php foreach($rows as $r): ?><tr><td>#<?=$r['ma_hd']?></td><td>#<?=$r['ma_dat_san']?></td><td><?=e($r['ho_ten'])?></td><td><?=date('d/m/Y H:i',strtotime($r['thoi_diem_lap']))?></td><td><?=e($r['phuong_thuc_thanh_toan'])?></td><td><b><?=money($r['so_tien_phai_tra'])?></b></td></tr><?php endforeach; ?></table></div><?php if(!$rows): ?><div class="empty">Chưa có hóa đơn.</div><?php endif; ?></div>

<?php elseif($page==='prices'): ?>
  <div class="page-head"><div><div class="eyebrow">CẤU HÌNH GIÁ</div><h1>Bảng giá thuê sân</h1><p>5 khung giờ hiện hành theo đề bài.</p></div></div>
  <div class="panel"><div class="table-wrap"><table><tr><th>Khung giờ</th><th>Đơn giá / giờ</th></tr><?php foreach($pdo->query("SELECT * FROM khung_gio_gia ORDER BY gio_bat_dau") as $r): ?><tr><td><?=substr($r['gio_bat_dau'],0,5)?> – <?=substr($r['gio_ket_thuc'],0,5)?></td><td><b><?=money($r['don_gia_gio'])?></b></td></tr><?php endforeach; ?></table></div><div class="callout">Giá của từng giờ thuê được sao lưu vào CHI_TIET_GIA_SAN tại thời điểm tạo lượt đặt để bảo toàn lịch sử.</div></div>

<?php elseif($page==='reports'): ?>
  <div class="page-head"><div><div class="eyebrow">PHÂN TÍCH DỮ LIỆU</div><h1>Thống kê & báo cáo</h1><p>Năm truy vấn Q1–Q5 theo yêu cầu phần B của báo cáo.</p></div></div>
  <?php
  $fmt='Y-m-d H:i:s';
  // ---- Q1: sân trống trong khoảng T1–T2
  $q1t1=trim($_GET['q1_t1']??''); $q1t2=trim($_GET['q1_t2']??''); $q1rows=null; $q1err='';
  if($q1t1!=='' && $q1t2!==''){
    try{
      $a1=new DateTimeImmutable($q1t1); $b1=new DateTimeImmutable($q1t2);
      if($b1<=$a1){ $q1err='Thời điểm kết thúc phải sau thời điểm bắt đầu.'; }
      else{
        $st=$pdo->prepare("SELECT s.ma_san FROM san_bong s WHERE NOT EXISTS (SELECT 1 FROM luot_dat_san l WHERE l.ma_san=s.ma_san AND l.trang_thai IN ('đã đặt','đang chơi','đã hoàn thành') AND l.thoi_gian_bat_dau < ? AND l.thoi_gian_ket_thuc > ?) ORDER BY s.ma_san");
        $st->execute([$b1->format($fmt),$a1->format($fmt)]); $q1rows=$st->fetchAll();
      }
    }catch(Throwable $x){ $q1err='Thời gian không hợp lệ.'; }
  }
  // ---- Q2: lịch sử đặt sân của một khách hàng
  $q2id=(int)($_GET['q2_kh']??0); $q2rows=null;
  if($q2id>0){
    $st=$pdo->prepare("SELECT k.ho_ten,k.tong_diem,k.cap_bac,s.ma_san,l.ma_dat_san,l.thoi_diem_tao,l.thoi_gian_bat_dau,l.thoi_gian_ket_thuc,l.tong_tien_san,l.tien_coc,l.hinh_thuc_thue,l.trang_thai FROM khach_hang k JOIN luot_dat_san l ON l.ma_kh=k.ma_kh JOIN san_bong s ON s.ma_san=l.ma_san WHERE k.ma_kh=? ORDER BY l.thoi_diem_tao DESC");
    $st->execute([$q2id]); $q2rows=$st->fetchAll();
  }
  // ---- Q3: doanh thu theo ngày hoặc tháng
  $q3mode=($_GET['q3_mode']??'day')==='month'?'month':'day';
  $inner="SELECT DATE(thoi_diem_lap) AS ngay, so_tien_phai_tra AS tien FROM hoa_don UNION ALL SELECT DATE(thoi_diem_tao) AS ngay, tien_coc AS tien FROM luot_dat_san WHERE tien_coc>0";
  $q3rows=$q3mode==='month'
    ? $pdo->query("SELECT DATE_FORMAT(ngay,'%Y-%m') AS ky, SUM(tien) AS tong FROM ($inner) x GROUP BY DATE_FORMAT(ngay,'%Y-%m') ORDER BY ky")->fetchAll()
    : $pdo->query("SELECT ngay AS ky, SUM(tien) AS tong FROM ($inner) x GROUP BY ngay ORDER BY ngay")->fetchAll();
  // ---- Q4: khách hàng Gold
  $gold=$pdo->query("SELECT * FROM khach_hang WHERE cap_bac='Gold Member' ORDER BY tong_diem DESC")->fetchAll();
  // ---- Q5: sân được đặt nhiều nhất trong khoảng ngày (hiển thị cả sân đồng hạng)
  $q5t1=trim($_GET['q5_t1']??'') ?: date('Y-m-d',strtotime('-90 days')); $q5t2=trim($_GET['q5_t2']??'') ?: date('Y-m-d'); $q5rows=[]; $q5err='';
  try{
    $a5=new DateTimeImmutable($q5t1); $b5=(new DateTimeImmutable($q5t2))->modify('+1 day');
    if($b5<=$a5){ $q5err='Ngày kết thúc phải từ ngày bắt đầu trở đi.'; }
    else{
      $p=[$a5->format('Y-m-d 00:00:00'),$b5->format('Y-m-d 00:00:00')];
      $st=$pdo->prepare("SELECT s.ma_san, COUNT(*) AS so_luot_hoan_thanh FROM san_bong s JOIN luot_dat_san l ON l.ma_san=s.ma_san WHERE l.trang_thai='đã hoàn thành' AND l.thoi_gian_bat_dau >= ? AND l.thoi_gian_bat_dau < ? GROUP BY s.ma_san HAVING COUNT(*) = (SELECT MAX(c) FROM (SELECT COUNT(*) AS c FROM luot_dat_san WHERE trang_thai='đã hoàn thành' AND thoi_gian_bat_dau >= ? AND thoi_gian_bat_dau < ? GROUP BY ma_san) t) ORDER BY s.ma_san");
      $st->execute(array_merge($p,$p)); $q5rows=$st->fetchAll();
    }
  }catch(Throwable $x){ $q5err='Ngày không hợp lệ.'; }
  ?>
  <div class="panel"><h3>Q1. Tìm sân trống theo khoảng thời gian</h3><p class="muted">Lượt đã hủy không chiếm sân; lượt đã đặt, đang chơi và đã hoàn thành có chiếm sân.</p>
    <form method="get" class="inline-form"><input type="hidden" name="page" value="reports"><label>Từ<input type="datetime-local" name="q1_t1" value="<?=e($q1t1)?>" required></label><label>Đến<input type="datetime-local" name="q1_t2" value="<?=e($q1t2)?>" required></label><button class="btn primary">Tìm sân trống</button></form>
    <?php if($q1err): ?><div class="alert error"><?=e($q1err)?></div><?php elseif($q1rows!==null): ?><?php if($q1rows): ?><div class="table-wrap"><table><tr><th>Sân còn trống</th></tr><?php foreach($q1rows as $r): ?><tr><td>Sân <?=$r['ma_san']?></td></tr><?php endforeach; ?></table></div><?php else: ?><div class="empty">Không có sân nào trống trong khoảng thời gian này.</div><?php endif; ?><?php endif; ?></div>

  <div class="panel"><h3>Q2. Lịch sử đặt sân của khách hàng</h3>
    <form method="get" class="inline-form"><input type="hidden" name="page" value="reports"><label>Khách hàng<select name="q2_kh" required><option value="">— chọn khách —</option><?php foreach($pdo->query("SELECT ma_kh,ho_ten,so_dien_thoai FROM khach_hang ORDER BY ho_ten") as $c): ?><option value="<?=$c['ma_kh']?>" <?=$q2id===(int)$c['ma_kh']?'selected':''?>><?=e($c['ho_ten'])?> · <?=e($c['so_dien_thoai'])?></option><?php endforeach; ?></select></label><button class="btn primary">Xem lịch sử</button></form>
    <?php if($q2rows!==null): ?><?php if($q2rows): ?><p class="muted"><b><?=e($q2rows[0]['ho_ten'])?></b> · <?=e($q2rows[0]['cap_bac'])?> · <?=$q2rows[0]['tong_diem']?> điểm · <?=count($q2rows)?> lượt đặt</p><div class="table-wrap"><table><tr><th>Mã đặt</th><th>Sân</th><th>Thời điểm tạo</th><th>Bắt đầu – kết thúc</th><th>Tiền sân</th><th>Cọc</th><th>Hình thức</th><th>Trạng thái</th></tr><?php foreach($q2rows as $r): ?><tr><td>#<?=$r['ma_dat_san']?></td><td>Sân <?=$r['ma_san']?></td><td><?=date('d/m/Y H:i',strtotime($r['thoi_diem_tao']))?></td><td><?=date('d/m H:i',strtotime($r['thoi_gian_bat_dau']))?> – <?=date('H:i',strtotime($r['thoi_gian_ket_thuc']))?></td><td><?=money($r['tong_tien_san'])?></td><td><?=money($r['tien_coc'])?></td><td><?=e($r['hinh_thuc_thue'])?></td><td><span class="status"><?=e($r['trang_thai'])?></span></td></tr><?php endforeach; ?></table></div><?php else: ?><div class="empty">Khách hàng này chưa có lượt đặt sân.</div><?php endif; ?><?php endif; ?></div>

  <div class="panel"><h3>Q3. Doanh thu theo <?=$q3mode==='month'?'tháng':'ngày'?></h3>
    <form method="get" class="inline-form"><input type="hidden" name="page" value="reports"><label>Thống kê theo<select name="q3_mode"><option value="day" <?=$q3mode==='day'?'selected':''?>>Ngày</option><option value="month" <?=$q3mode==='month'?'selected':''?>>Tháng</option></select></label><button class="btn primary">Xem</button></form>
    <div class="table-wrap" style="max-height:340px;overflow:auto"><table><tr><th><?=$q3mode==='month'?'Tháng':'Ngày'?></th><th>Doanh thu</th></tr><?php foreach($q3rows as $r): ?><tr><td><?=$q3mode==='month'?e($r['ky']):date('d/m/Y',strtotime($r['ky']))?></td><td><?=money($r['tong'])?></td></tr><?php endforeach; ?></table></div><?php if(!$q3rows): ?><div class="empty">Chưa có dữ liệu doanh thu.</div><?php endif; ?>
    <div class="callout">Doanh thu = tiền khách trả trên hóa đơn (theo ngày lập) + tiền cọc (theo ngày tạo lượt đặt), kể cả cọc của lượt sau đó bị hủy.</div></div>

  <div class="grid two align-start">
    <div class="panel"><h3>Q4. Khách hàng Gold</h3><?php if($gold): ?><table><tr><th>Khách hàng</th><th>Số điện thoại</th><th>Điểm</th></tr><?php foreach($gold as $g): ?><tr><td><?=e($g['ho_ten'])?></td><td><?=e($g['so_dien_thoai'])?></td><td><?=$g['tong_diem']?></td></tr><?php endforeach; ?></table><?php else: ?><div class="empty">Chưa có khách Gold.</div><?php endif; ?></div>
    <div class="panel"><h3>Q5. Sân được đặt nhiều nhất</h3><p class="muted">Tính theo lượt đã hoàn thành; sân đồng hạng đều được hiển thị.</p>
      <form method="get" class="inline-form"><input type="hidden" name="page" value="reports"><label>Từ ngày<input type="date" name="q5_t1" value="<?=e($q5t1)?>"></label><label>Đến ngày<input type="date" name="q5_t2" value="<?=e($q5t2)?>"></label><button class="btn primary">Xem</button></form>
      <?php if($q5err): ?><div class="alert error"><?=e($q5err)?></div><?php elseif($q5rows): ?><table><tr><th>Sân</th><th>Lượt hoàn thành</th></tr><?php foreach($q5rows as $r): ?><tr><td>Sân <?=$r['ma_san']?></td><td><?=$r['so_luot_hoan_thanh']?></td></tr><?php endforeach; ?></table><?php else: ?><div class="empty">Không có lượt hoàn thành trong khoảng ngày này.</div><?php endif; ?></div>
  </div>
<?php endif; ?>
</section>
<footer>© <?=date('Y')?> FootballField · Demo BTL Cơ sở dữ liệu · Nhóm 20</footer>
</main></div>
<script src="assets/app.js"></script>
</body></html>