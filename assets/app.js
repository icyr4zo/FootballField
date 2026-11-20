document.addEventListener('DOMContentLoaded',()=>{
 const type=document.getElementById('bookingType');
 const deposit=document.getElementById('depositMethod');
 if(type&&deposit){const sync=()=>deposit.style.display=type.value==='đặt trước'?'flex':'none';type.addEventListener('change',sync);sync();}
 const payment=document.getElementById('paymentMethod');
 const qrModal=document.getElementById('demoQrModal');
 const closeQr=()=>{if(qrModal){qrModal.classList.remove('show');qrModal.setAttribute('aria-hidden','true');}};
 const openQr=()=>{if(qrModal){qrModal.classList.add('show');qrModal.setAttribute('aria-hidden','false');}};
 if(payment&&qrModal){
   payment.addEventListener('change',()=>{if(payment.value==='chuyển khoản')openQr();});
   document.getElementById('closeQr')?.addEventListener('click',closeQr);
   document.getElementById('closeQrDone')?.addEventListener('click',closeQr);
   qrModal.addEventListener('click',e=>{if(e.target===qrModal)closeQr();});
   document.addEventListener('keydown',e=>{if(e.key==='Escape')closeQr();});
 }
 const dt=document.querySelector('input[name="thoi_gian_bat_dau"]');
 if(dt && !dt.value){const d=new Date();d.setMinutes(0,0,0);d.setHours(d.getHours()+1);const local=new Date(d.getTime()-d.getTimezoneOffset()*60000);dt.value=local.toISOString().slice(0,16);}
});

// Cập nhật danh sách sân theo khoảng thời gian được chọn; sân có lịch giao nhau
// sẽ bị ẩn khỏi lựa chọn (server vẫn kiểm tra lại trước khi lưu).
document.addEventListener('DOMContentLoaded',()=>{
 const form=document.getElementById('bookingForm');
 if(!form || !window.bookingAvailability) return;
 const field=document.getElementById('bookingField');
 const start=document.getElementById('bookingStart');
 const hours=document.getElementById('bookingHours');
 const hint=document.getElementById('fieldAvailabilityHint');
 const records=window.bookingAvailability.bookings||[];
 const updateFields=()=>{
   if(!field||!start||!hours||!start.value) return;
   const from=new Date(start.value).getTime();
   const to=from+(Number(hours.value)||1)*3600000;
   let available=0;
   [...field.options].forEach(opt=>{
     const id=Number(opt.value);
     const busy=records.some(b=>Number(b.ma_san)===id &&
       new Date(String(b.thoi_gian_bat_dau).replace(' ','T')).getTime()<to &&
       new Date(String(b.thoi_gian_ket_thuc).replace(' ','T')).getTime()>from);
     const isSelected=opt.value===field.value;
     opt.hidden=busy&&!isSelected;
     opt.disabled=busy&&!isSelected;
     const label='Sân '+id;
     opt.textContent=busy?(label+' · Đã có lịch trong khung giờ'):label;
     if(!busy) available++;
   });
   hint.textContent=available?('Có '+available+' sân còn trống trong khung giờ này.'):'Không có sân trống trong khung giờ này. Vui lòng đổi thời gian hoặc số giờ thuê.';
 };
 start?.addEventListener('change',updateFields);
 hours?.addEventListener('change',updateFields);
 updateFields();
});
