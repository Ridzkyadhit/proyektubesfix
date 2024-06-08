// Pengaturan ukuran kalender
const calendarEl = document.getElementById('calendar');
let currentCalendarHeight = 400; // Ukuran awal kalender

const btnKecil = document.getElementById('btn-kecil');
const btnBesar = document.getElementById('btn-besar');

btnKecil.addEventListener('click', () => {
  currentCalendarHeight -= 50; // Perkecil tinggi kalender
  calendarEl.style.height = currentCalendarHeight + 'px';
});

btnBesar.addEventListener('click', () => {
  currentCalendarHeight += 50; // Perbesar tinggi kalender
  calendarEl.style.height = currentCalendarHeight + 'px';
});
