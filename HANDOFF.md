# Handoff — ClinicMS (IT9AL Final Project)

## Goal

Build a full Laravel 11 clinic management web app as a final project. Core features:

- **Patients** — records, assigned nurse/doctor, visit history, QR patient cards
- **Medicines** — inventory with smart scanning (QR code / barcode via camera or image upload), stock alerts, expiry tracking
- **Staff** — directory, role-based access, monthly shift calendar
- **Chat** — real-time direct messages between staff, group sidebar, own-message deletion
- **Roles** — Admin, Clinic Head, Doctor, Pharmacist, Nurse, Secretary, Assistant (each with appropriate permissions)

The app is a solo project. Design language is card-based, Tailwind CSS (CDN, no build step), Font Awesome 6.5, dark mode supported throughout.

---

## Current State

The app is **feature-complete for the demo**. Everything listed above works. The most recent batch of work added:

- Three new roles: **Clinic Head**, **Pharmacist**, **Secretary** — with correct permissions, proper display labels (no snake_case anywhere in the UI), and role color coding throughout
- **Add Medicine form** completely redesigned as a smart scan page — camera scanner, image upload/paste, JSON + pipe-separated QR auto-fill, custom styled dropdowns, datalist autocomplete for storage locations
- Two new medicine columns: `brand_names` (trade names) and `dosage_form` (tablet/capsule/etc)
- **Delete own messages** in chat — hover to reveal, click to remove
- Demo login quick-fill grid updated to show all 7 roles

### Database

Two migrations were added this session and have been run locally:

| File | What it does |
|---|---|
| `2026_05_13_100000_add_image_to_medicines.php` | adds `image_path` column |
| `2026_05_13_200000_add_medicine_extras.php` | adds `brand_names` and `dosage_form` columns |

**Anyone pulling this repo for the first time (or after a gap) must run `php artisan migrate` before serving.** Without it, the Add Medicine form will throw a SQL error on save.

### Demo Accounts (all use password: `password`)

| Role | Email |
|---|---|
| Admin | admin@clinic.com |
| Clinic Head | clinichead@clinic.com |
| Doctor | doctor@clinic.com |
| Pharmacist | pharmacist@clinic.com |
| Nurse | nurse@clinic.com |
| Secretary | secretary@clinic.com |
| Assistant | assistant@clinic.com |

The Clinic Head, Pharmacist, and Secretary accounts only exist if the database was seeded with `DemoDataSeeder`. If the quick-login buttons for those three don't work, see the setup section below.

---

## Files Actively Edited This Session

Every file listed here has meaningful changes — don't overwrite them lightly:

| File | What changed |
|---|---|
| `app/Models/User.php` | Added `roleLabel()` method; updated `can_()` permissions for all 7 roles |
| `app/Models/Medicine.php` | Added `brand_names`, `dosage_form` to `$fillable` |
| `app/Http/Controllers/ScanController.php` | Handles new fields, image scanning, structured QR parsing |
| `app/Http/Controllers/ChatController.php` | Added `destroyMessage()` + role ordering for all 7 roles |
| `app/Http/Controllers/AuthController.php` | Back-button login protection (session flash guard) |
| `routes/web.php` | Added `DELETE /chat/messages/{message}` route |
| `resources/views/scan/index.blade.php` | Entire Add Medicine page — scanner, custom dropdowns, auto-fill JS |
| `resources/views/chat/index.blade.php` | Delete button, all 7 role colors, hover-reveal via JS |
| `resources/views/auth/login.blade.php` | 7-account quick-login grid |
| `resources/views/staff/index.blade.php` | All 7 roles in filters and add-staff modal |
| `resources/views/staff/show.blade.php` | Role display, shift calendar |
| `resources/views/profile/edit.blade.php` | Role label display |
| `resources/views/patients/index.blade.php` | Role label display |
| `resources/views/dashboard.blade.php` | Role-aware stat cards |
| `resources/views/medicines/index.blade.php` | Stock alerts, highlight-on-redirect animation |
| `resources/views/medicines/show.blade.php` | Medicine detail page |
| `resources/views/layouts/app.blade.php` | Sidebar nav with role gating |
| `database/seeders/DemoDataSeeder.php` | Added Clinic Head, Pharmacist, Secretary demo users |
| `database/migrations/2026_05_13_100000_add_image_to_medicines.php` | New — must migrate |
| `database/migrations/2026_05_13_200000_add_medicine_extras.php` | New — must migrate |

---

## Things Tried That Failed

### Tailwind `group-hover` in CDN mode
Tailwind CDN does not run JIT — arbitrary group-hover variants like `group-hover/msg:opacity-100` are not generated at runtime. Tried it, elements stayed invisible. Fixed by replacing with plain JS `mouseover`/`mouseout` event delegation and a hardcoded `opacity:0` inline style on the delete button.

### `overflow: hidden` on `.form-section` cutting off the dropdown panel
The custom dropdown opens a panel with `position: absolute`. When the parent section had `overflow: hidden`, the panel was clipped at the section border and only 1-2 options were visible. Tried `overflow: visible` alone — the section header's colored background then bled past the rounded corners. Fixed with: `overflow: visible` on the section + `border-radius: calc(1.25rem - 2px) calc(1.25rem - 2px) 0 0` on the header element so its background clips itself without relying on the parent's overflow clip.

### Native `<select>` styling
The browser's native select popup is OS-rendered and cannot be styled with CSS — it always opens as a plain rectangular box. Tried `appearance: none` with custom arrow images (it works for the closed state), but the open dropdown popup is always native. Fixed by hiding the native select entirely (`position:absolute; opacity:0; width:1px`) and building a fully custom trigger button + panel in JS with `initCustomSelects()`.

### `name="type"` conflict on dosage form
The form had `name="type"` on the dosage form select, which conflicted with the medicine model's `type` column (prescription/OTC). The controller was saving the wrong value. Fixed by renaming to `name="dosage_form"` in the HTML and updating the controller validation and the JS `autofillFromParsed()` accordingly.

---

## Next Steps

The app is in a good state for demo. If continuing:

1. **Shift management UI** — admins can currently only view shifts; a proper add/edit shift form for the Clinic Head role would round it out
2. **Prescription flow** — when a doctor dispenses a controlled/Rx medicine, log it against a patient record
3. **Reports page** — monthly inventory usage summary (who dispensed what, when)
4. **Real-time chat polish** — messages currently update on page reload for non-JS path; could wire up Laravel Echo + Pusher for true real-time if the school server supports it

---

## Setup Checklist (for anyone cloning or pulling)

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy env and generate key (first time only)
cp .env.example .env
php artisan key:generate

# 3. Set DB credentials in .env
# DB_DATABASE=clinic_db  DB_USERNAME=root  DB_PASSWORD=

# 4. Run ALL migrations — required, two new ones were added recently
php artisan migrate

# 5. Seed demo data (creates all 7 demo accounts + sample patients/medicines)
php artisan db:seed --class=DemoDataSeeder

# 6. Serve
php artisan serve
```

> If you already have data and don't want to wipe it, skip step 5 — but the Clinic Head, Pharmacist, and Secretary quick-login buttons on the login page won't work until those users exist in the DB.

> To start completely fresh (wipes all data): `php artisan migrate:fresh --seed`
