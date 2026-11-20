<?php
declare(strict_types=1);
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');

const DB_HOST = '127.0.0.1';
const DB_NAME = 'footballfield';
const DB_USER = 'root';
const DB_PASS = ''; // XAMPP mặc định thường để trống

// Tài khoản quản trị dùng chung cho bản demo. Đổi mật khẩu bằng cách tạo hash mới
// với password_hash('MatKhauMoi', PASSWORD_DEFAULT) rồi thay giá trị bên dưới.
const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD_HASH = '$2y$12$KTmmbxpRZ19cLcfEZd.8AOG6sGvmwtpxWDnvSV38tLX9M8lklQzj6';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+07:00'",
            ]
        );
    }
    return $pdo;
}
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function money($n): string { return number_format((float)$n, 0, ',', '.') . ' đ'; }
function redirect(string $page='dashboard', string $msg=''): never {
    if ($msg !== '') $_SESSION['flash'] = $msg;
    header('Location: index.php?page='.urlencode($page));
    exit;
}
function flash(): string {
    $m = $_SESSION['flash'] ?? '';
    unset($_SESSION['flash']);
    return $m;
}
function currentTier(int $points): array {
    if ($points >= 150) return ['Gold Member', 0.10];
    if ($points >= 50) return ['Silver Member', 0.05];
    return ['Bronze Member', 0.00];
}
function expireNoShows(): void {
    // Quy tắc: quá 30 phút sau giờ bắt đầu mà vẫn "đã đặt" thì tự động hủy.
    db()->exec("UPDATE luot_dat_san SET trang_thai='đã hủy'
                WHERE trang_thai='đã đặt' AND thoi_gian_bat_dau < DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
}

function staffOnDuty(int $staffId, string $moment): bool {
    $q = db()->prepare("SELECT c.gio_bat_dau,c.gio_ket_thuc
        FROM nhan_vien n JOIN ca_lam c ON c.ma_ca=n.ma_ca WHERE n.ma_nv=?");
    $q->execute([$staffId]); $shift=$q->fetch();
    if (!$shift) return false;
    $time = (new DateTimeImmutable($moment))->format('H:i:s');
    $start = $shift['gio_bat_dau']; $end = $shift['gio_ket_thuc'];
    // Ca qua đêm, ví dụ 18:00–06:00.
    return $start <= $end ? ($time >= $start && $time < $end) : ($time >= $start || $time < $end);
}

function nextWholeHour(DateTimeImmutable $now): DateTimeImmutable {
    $hour = $now->setTime((int)$now->format('H'), 0, 0);
    return $hour->modify('+1 hour');
}
function priceForBooking(DateTimeImmutable $start, int $hours): array {
    $pdo = db();
    $total = 0; $rows = [];
    for ($i=0; $i<$hours; $i++) {
        $slot = $start->modify("+$i hour");
        $time = $slot->format('H:i:s');
        $q = $pdo->prepare("SELECT ma_khung_gio, don_gia_gio FROM khung_gio_gia
            WHERE (gio_bat_dau <= gio_ket_thuc AND TIME(?) >= gio_bat_dau AND TIME(?) < gio_ket_thuc)
               OR (gio_bat_dau > gio_ket_thuc AND (TIME(?) >= gio_bat_dau OR TIME(?) < gio_ket_thuc))
            LIMIT 1");
        $q->execute([$time,$time,$time,$time]);
        $p = $q->fetch();
        if (!$p) throw new RuntimeException("Chưa cấu hình giá cho giờ ".$slot->format('H:i'));
        $total += (float)$p['don_gia_gio'];
        $rows[] = ['slot'=>$slot->format('Y-m-d H:i:s'), 'ma_khung_gio'=>$p['ma_khung_gio'], 'price'=>$p['don_gia_gio']];
    }
    return [$total, $rows];
}
