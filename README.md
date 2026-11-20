# FootballField – Demo quản lý sân bóng 7 người

Dự án demo BTL Cơ sở dữ liệu – Nhóm 20. Công nghệ: PHP 8+, MySQL 8+/MariaDB, HTML/CSS/JS, XAMPP.

## 1. Cài đặt bằng XAMPP
1. Cài XAMPP, mở Control Panel và bật **Apache** + **MySQL**.
2. Giải nén thư mục `FootballField` vào `C:\xampp\htdocs\`.
3. Mở `http://localhost/phpmyadmin`.
4. Chọn tab **Import**, chọn file `FootballField/database/footballfield.sql`, nhấn Import.
5. Kiểm tra `config.php`: mặc định host `127.0.0.1`, database `footballfield`, user `root`, password rỗng.
6. Mở `http://localhost/FootballField/`.

## 2. Tài khoản đăng nhập
Toàn bộ trang quản lý yêu cầu đăng nhập bằng một tài khoản quản trị dùng chung cho nhân viên:
- **Tên đăng nhập:** `admin`
- **Mật khẩu demo:** `Football@2026`

Mật khẩu được lưu dưới dạng hash bằng `password_hash()` và xác minh bằng `password_verify()`. Có thể đổi mật khẩu bằng cách tạo hash mới trong PHP (`password_hash('MatKhauMoi', PASSWORD_DEFAULT)`) rồi thay `ADMIN_PASSWORD_HASH` trong `config.php`. Đăng xuất bằng nút ở góc trên bên phải. Đây là tài khoản dùng chung phục vụ demo; nếu triển khai thật nên tạo tài khoản riêng cho từng nhân viên, thêm phân quyền, CSRF token cho mọi biểu mẫu và nhật ký thao tác.

## 3. Dữ liệu mẫu
Khớp Bảng 3.3 của báo cáo: CA_LAM 2, NHAN_VIEN 5, KHACH_HANG 10, SAN_BONG 4, KHUNG_GIO_GIA 5, DICH_VU 3, LUOT_DAT_SAN 129, CHI_TIET_GIA_SAN 241, CHI_TIET_DICH_VU 198, HOA_DON 117.
- 126 lượt trong quá khứ (17/08–04/11/2026): 117 đã hoàn thành (có hóa đơn) và 9 đã hủy. Điểm và cấp bậc khách (1 Gold, 2 Silver, còn lại Bronze; không có hồ sơ khách vãng lai dùng chung — khách vãng lai cũng được thêm bằng số điện thoại riêng) được tính từ chính các hóa đơn.
- 3 lượt tính theo thời điểm import để demo ngay: #127, #128 ở trạng thái đã đặt (ngày mai) và **#129 đang chơi** (dùng để demo dịch vụ và hóa đơn).
- 5 khung giá: 00–06 (150.000đ), 06–10 (200.000đ), 10–15 (250.000đ), 15–18 (270.000đ), 18–24 (300.000đ).
- 3 dịch vụ: thuê bóng, thuê áo bib, nước uống.
- Dữ liệu 126 lượt là dữ liệu mô phỏng nhất quán theo quy tắc nghiệp vụ; số liệu chi tiết có thể khác các hình minh họa trong báo cáo.

## 4. Luồng demo gợi ý
1. Dashboard: giới thiệu số sân, khách hàng, lượt đặt và doanh thu.
2. Khách hàng: giới thiệu hồ sơ và cấp bậc.
3. Bảng giá: giới thiệu 5 khung giá.
4. Tạo lượt đặt: chọn khách, sân, giờ bắt đầu, số giờ. Hệ thống tính tổng tiền và cọc 30%.
5. Lượt đặt sân: tra cứu theo số điện thoại khách; nhận sân phải nhập đúng số điện thoại của khách đã đặt, hoặc hủy lượt.
6. Dịch vụ: khi lượt đang chơi, thêm bóng/áo bib/nước.
7. Hóa đơn: mở thanh toán, kiểm tra tiền sân, giảm giá, cọc, dịch vụ; xác nhận thanh toán.
8. Khách hàng: quay lại kiểm tra điểm và cấp bậc sau thanh toán.
9. Thống kê: giới thiệu Gold và sân đặt nhiều.

## 5. Lưu ý nghiệp vụ / giới hạn bản demo
- Kiểm tra trùng giờ, thời lượng 1–3 giờ, giờ bắt đầu tròn, cọc 30%, không thu cọc với thuê trực tiếp, chỉ nhận sân trong khoảng thời gian hợp lệ, chỉ thêm dịch vụ khi đang chơi, chỉ lập hóa đơn khi đang chơi, cộng điểm và cập nhật cấp bậc được xử lý trong PHP.
- Tự động hủy lượt đặt quá 30 phút sau giờ bắt đầu được kiểm tra mỗi khi có trang được tải. Để vận hành thực tế nên cấu hình cron job chạy định kỳ.
- Ràng buộc nhân viên đang trong ca tại thời điểm tiếp nhận/lập hóa đơn, kiểm tra phủ đủ 24 giờ và không chồng lấn khung giá, và đồng bộ cột tình trạng sân là các quy tắc cần hoàn thiện thêm bằng trigger/logic server. DBML/SQL thể hiện khóa và CHECK cơ bản.
- Bản demo đã có đăng nhập một tài khoản quản trị dùng chung; chưa có tài khoản riêng/phân quyền theo nhân viên, CSRF token cho toàn bộ biểu mẫu, nhật ký kiểm toán hoặc cấu hình production. Không đưa lên Internet hay dùng cho thanh toán thật.
- Bản demo dùng dữ liệu và giá mẫu phục vụ thuyết trình; có thể chỉnh sửa theo dữ liệu nhóm mong muốn.

## 6. Khắc phục lỗi thường gặp
- `could not find driver`: bật extension `pdo_mysql` trong `php.ini`, khởi động lại Apache.
- `Unknown database`: import lại `footballfield.sql`.
- `Access denied`: kiểm tra user/password MySQL trong `config.php`.
- Trang trắng: kiểm tra PHP version và bật `display_errors` tạm thời trong môi trường local.
