<?php
// Màn hình đăng nhập độc lập, không tải dữ liệu quản lý khi chưa xác thực.
if (empty($_SESSION['login_csrf'])) $_SESSION['login_csrf'] = bin2hex(random_bytes(32));
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng nhập | FootballField</title>
<style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:linear-gradient(135deg,#101c2e,#173c39);font-family:Inter,Segoe UI,Arial,sans-serif;color:#172033}
.login-card{width:min(100%,430px);background:#fff;border-radius:22px;padding:38px;box-shadow:0 24px 70px #0005}.brand{display:flex;align-items:center;gap:13px;margin-bottom:30px}.brand-icon{width:48px;height:48px;border-radius:15px;display:grid;place-items:center;background:#e7f7ef;font-size:26px}.brand b{display:block;font-size:20px}.brand small{display:block;margin-top:4px;color:#82909f;font-size:10px;letter-spacing:1.5px}.eyebrow{font-size:11px;font-weight:800;letter-spacing:1.5px;color:#169b67}.login-card h1{font-size:28px;margin:9px 0}.desc{color:#778395;font-size:14px;margin:0 0 26px;line-height:1.6}label{display:block;font-size:13px;font-weight:700;margin:17px 0 7px}input{width:100%;padding:13px 14px;border:1px solid #dce3e9;border-radius:10px;font-size:15px;outline:none}input:focus{border-color:#19a36b;box-shadow:0 0 0 3px #19a36b20}.submit{width:100%;border:0;border-radius:10px;padding:14px;margin-top:24px;background:#159765;color:white;font-size:15px;font-weight:800;cursor:pointer}.submit:hover{background:#107b53}.error{background:#fff0f0;color:#b42318;border:1px solid #ffd4d1;border-radius:9px;padding:11px 13px;font-size:13px;margin:16px 0}.foot{text-align:center;color:#9aa4b2;font-size:12px;margin-top:24px}.hint{font-size:12px;color:#7b8794;margin-top:16px;line-height:1.5}
</style>
</head>
<body>
<main class="login-card">
  <div class="brand"><div class="brand-icon">⚽</div><div><b>FootballField</b><small>FIELD MANAGEMENT SYSTEM</small></div></div>
  <div class="eyebrow">KHU VỰC NỘI BỘ</div>
  <h1>Đăng nhập hệ thống</h1>
  <p class="desc">Vui lòng đăng nhập bằng tài khoản quản trị được cấp để tiếp tục quản lý sân bóng.</p>
  <?php if (!empty($loginError)): ?><div class="error"><?=e($loginError)?></div><?php endif; ?>
  <form method="post" autocomplete="on">
    <input type="hidden" name="action" value="login">
    <input type="hidden" name="csrf" value="<?=e($_SESSION['login_csrf'])?>">
    <label for="username">Tên đăng nhập</label>
    <input id="username" name="username" type="text" autocomplete="username" required autofocus>
    <label for="password">Mật khẩu</label>
    <input id="password" name="password" type="password" autocomplete="current-password" required>
    <button class="submit" type="submit">Đăng nhập →</button>
  </form>
  <div class="hint">Chỉ nhân viên được cấp tài khoản mới có thể truy cập khu vực quản lý.</div>
  <div class="foot">© FootballField · Demo BTL CSDL · Nhóm 20</div>
</main>
</body>
</html>
