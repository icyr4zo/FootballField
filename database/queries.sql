USE footballfield;

-- 1. Tìm sân trống trong khoảng :T1 đến :T2
SELECT s.ma_san
FROM san_bong s
WHERE NOT EXISTS (
 SELECT 1 FROM luot_dat_san l
 WHERE l.ma_san=s.ma_san
   AND l.trang_thai IN ('đã đặt','đang chơi','đã hoàn thành')
   AND l.thoi_gian_bat_dau < @T2
   AND l.thoi_gian_ket_thuc > @T1
);

-- 2. Lịch sử đặt sân của khách hàng :ma_kh
SELECT k.ho_ten,k.tong_diem,k.cap_bac,s.ma_san,l.*
FROM khach_hang k
JOIN luot_dat_san l ON l.ma_kh=k.ma_kh
JOIN san_bong s ON s.ma_san=l.ma_san
WHERE k.ma_kh=@ma_kh
ORDER BY l.thoi_diem_tao DESC;

-- 3. Doanh thu theo ngày: tiền hóa đơn + tiền cọc (kể cả lượt hủy)
SELECT ngay, SUM(tien) AS tong_doanh_thu
FROM (
 SELECT DATE(thoi_diem_lap) AS ngay, so_tien_phai_tra AS tien FROM hoa_don
 UNION ALL
 SELECT DATE(thoi_diem_tao) AS ngay, tien_coc AS tien
 FROM luot_dat_san WHERE tien_coc>0
) x
GROUP BY ngay ORDER BY ngay;

-- 3b. Doanh thu theo tháng
SELECT DATE_FORMAT(ngay, '%Y-%m') AS thang, SUM(tien) AS tong_doanh_thu
FROM (
 SELECT DATE(thoi_diem_lap) AS ngay, so_tien_phai_tra AS tien FROM hoa_don
 UNION ALL
 SELECT DATE(thoi_diem_tao) AS ngay, tien_coc AS tien
 FROM luot_dat_san WHERE tien_coc>0
) x
GROUP BY DATE_FORMAT(ngay, '%Y-%m') ORDER BY thang;

-- 4. Khách hàng Gold
SELECT * FROM khach_hang
WHERE cap_bac='Gold Member'
ORDER BY tong_diem DESC;

-- 5. Sân được đặt nhiều nhất theo khoảng ngày, đồng hạng nhất đều hiển thị
SELECT s.ma_san, COUNT(*) AS so_luot_hoan_thanh
FROM san_bong s JOIN luot_dat_san l ON l.ma_san=s.ma_san
WHERE l.trang_thai='đã hoàn thành'
  AND l.thoi_gian_bat_dau >= @T1
  AND l.thoi_gian_bat_dau < @T2
GROUP BY s.ma_san
HAVING COUNT(*) = (
 SELECT MAX(cnt) FROM (
   SELECT COUNT(*) cnt FROM luot_dat_san
   WHERE trang_thai='đã hoàn thành'
     AND thoi_gian_bat_dau >= @T1 AND thoi_gian_bat_dau < @T2
   GROUP BY ma_san
 ) x
);
