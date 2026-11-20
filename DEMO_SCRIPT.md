# KỊCH BẢN THUYẾT TRÌNH DEMO FOOTBALLFIELD

## Mở đầu (30–45 giây)
“Nhóm em xây dựng FootballField – hệ thống quản lý cho thuê sân bóng đá 7 người. Website kết nối PHP với MySQL, mô phỏng các nghiệp vụ từ quản lý khách hàng, đặt sân, nhận sân, dịch vụ đến thanh toán và thống kê.”

## Trình tự thao tác (khoảng 5–7 phút)
1. **Dashboard:** giới thiệu các chỉ số tổng quan.
2. **Khách hàng:** chỉ ra số điện thoại là duy nhất; giới thiệu Bronze/Silver/Gold.
3. **Bảng giá:** chỉ ra 5 khung giá theo đề.
4. **Tạo lượt đặt:** chọn khách hàng, sân, thời gian bắt đầu là giờ tròn và 2 giờ. Giải thích hệ thống kiểm tra trùng lịch và tính tiền theo từng giờ. Với khung 18h–24h, ví dụ 19h–21h là 600.000đ, cọc 30% là 180.000đ.
5. **Lượt đặt sân:** tra cứu theo số điện thoại; khi khách đến, nhập đúng số điện thoại để nhận sân. giới thiệu lượt #129 được nạp sẵn ở trạng thái đang chơi để không phải chờ đến giờ nhận sân. Có thể tạo thêm lượt mới để minh họa quy trình đặt trước và kiểm tra trùng giờ.
6. **Dịch vụ:** mở Dịch vụ ở lượt #129, thêm 1 bóng và 2 nước uống.
7. **Hóa đơn:** minh họa công thức: tiền sân − giảm giá tiền sân − cọc + dịch vụ. Xác nhận thanh toán.
8. **Khách hàng:** quay lại kiểm tra điểm tăng bằng floor(số tiền phải trả / 100.000) và cấp bậc.
9. **Thống kê:** lần lượt Q1 (sân trống), Q2 (lịch sử khách), Q3 (doanh thu theo ngày/tháng), Q4 (khách Gold), Q5 (sân đặt nhiều nhất theo khoảng ngày).

## Câu hỏi giảng viên có thể hỏi
- Vì sao có CHI_TIET_GIA_SAN? Để lưu giá từng giờ tại thời điểm đặt, không bị thay đổi khi bảng giá cập nhật.
- Vì sao HOA_DON.ma_dat_san UNIQUE? Mỗi lượt đặt tối đa một hóa đơn.
- Vì sao CHI_TIET_DICH_VU có khóa kép? Một dịch vụ chỉ có một dòng trên mỗi lượt; gọi thêm thì cộng số lượng.
- Tại sao tiền cọc không hoàn lại? Theo quy tắc nghiệp vụ trong đề.
- Điểm được tính thế nào? Mỗi 100.000đ khách thực trả trên hóa đơn được 1 điểm, làm tròn xuống.
- Tại sao không dùng tình trạng sân để tìm sân trống theo giờ? Vì phải xét các lượt đặt có thời gian chồng lấn trong khoảng truy vấn.
