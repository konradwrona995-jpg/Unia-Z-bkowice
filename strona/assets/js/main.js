// Menu mobilne
const burger = document.querySelector('.burger');
const nav = document.querySelector('.nav');
burger?.addEventListener('click', () => {
  const open = nav.classList.toggle('open');
  burger.setAttribute('aria-expanded', open);
});
nav?.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
  nav.classList.remove('open');
  burger.setAttribute('aria-expanded', false);
}));

// Odliczanie do najbliższego meczu
const next = document.querySelector('.nextmatch[data-start]');
if (next && next.dataset.start) {
  const start = new Date(next.dataset.start);
  const out = k => next.querySelector(`[data-cd="${k}"]`);
  const tick = () => {
    let s = Math.max(0, Math.floor((start - Date.now()) / 1000));
    const v = { d: Math.floor(s / 86400), h: Math.floor(s / 3600) % 24, m: Math.floor(s / 60) % 60, s: s % 60 };
    for (const k in v) if (out(k)) out(k).textContent = String(v[k]).padStart(2, '0');
  };
  tick();
  setInterval(tick, 1000);

  // Plik .ics – mecz trafia do kalendarza w telefonie
  next.querySelector('[data-ics]')?.addEventListener('click', () => {
    const fmt = d => d.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
    const end = new Date(start.getTime() + 2 * 3600 * 1000);
    const ics = [
      'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//KS Unia Zabkowice//PL', 'BEGIN:VEVENT',
      `UID:${start.getTime()}@uniazabkowice`, `DTSTAMP:${fmt(new Date())}`,
      `DTSTART:${fmt(start)}`, `DTEND:${fmt(end)}`,
      `SUMMARY:${next.dataset.title}`, `LOCATION:${next.dataset.place}`,
      'END:VEVENT', 'END:VCALENDAR',
    ].join('\r\n');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([ics], { type: 'text/calendar' }));
    a.download = 'mecz-unia-zabkowice.ics';
    a.click();
  });
}

// Rozwijanie listy meczów (terminarz / wyniki)
document.querySelectorAll('button.expand').forEach(btn => btn.addEventListener('click', () => {
  const open = btn.getAttribute('aria-expanded') !== 'true';
  btn.previousElementSibling.querySelectorAll('li.extra').forEach(li => li.hidden = !open);
  btn.setAttribute('aria-expanded', open);
  btn.textContent = open ? btn.dataset.less : btn.dataset.more;
  if (!open) btn.previousElementSibling.scrollIntoView({ block: 'start' });
}));
