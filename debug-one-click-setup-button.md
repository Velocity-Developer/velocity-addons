# Debug Session: one-click-setup-button

Status: OPEN

## Symptom
Klik tombol `Run 1 Click setup` tidak menampilkan perubahan apa pun.

## Hypotheses
1. JS handler `initOneClickSetupPage()` tidak pernah jalan karena `page` tidak match `velocity_one_click_setup`.
2. Script `velocity-addons-admin-actions.js` tidak termuat di halaman `sub=1-click-setup`.
3. Tombol ada, tapi event listener gagal attach karena DOM target tidak ketemu.
4. Request REST tidak pernah terkirim karena `restBase`/nonce kosong.
5. Ada error JS sebelum blok 1 Click setup jalan.

## Plan
- Start debug server
- Add instrumentation only
- Reproduce click
- Read logs
- Determine root cause
- Apply minimal fix
