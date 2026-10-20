# HMS — Hotel Management System

Laravel 12 + Tailwind CSS + Alpine.js system for running a hotel: rooms, bookings, front desk, billing, and back-office modules.

## Requirements

- PHP 8.2+ (developed on 8.5)
- MySQL 8+ (the schema uses MySQL generated columns; SQLite is not supported)
- Composer, Node.js 20+

## Setup

```bash
composer install
npm install
cp .env.example .env          # defaults to MySQL database "hms" on 127.0.0.1, user root
php artisan key:generate
php artisan migrate --seed    # creates roles, departments and admin@hotel.com / password
npm run dev                   # Vite dev server (keep running)
php artisan serve --port=8001
```

## Tests

Tests run against a separate MySQL database, `hms_test` (see `phpunit.xml`). Create it once:

```sql
CREATE DATABASE hms_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then `php artisan test`. The first test builds the schema and takes a few minutes.

## Roles

Access is by role (`app/Http/Middleware/EnsureUserHasRole.php`, applied in `routes/web.php`). Admins can do everything.

| Role | Can use |
|---|---|
| receptionist | Guests, bookings, check-in/out, payments, rooms, facilities, tasks |
| housekeeping | Rooms (status), tasks |
| manager | Everything except users and system settings |
| finance | Finance, invoices, payments, reports, stay payments |
| kitchen / procurement / hr | Their own modules and tasks |
| guest | Their own dashboard and profile |

## How modules are built

Each module follows the same pattern ("five files"): migration → model (+ enum for statuses) → controller → `index` / `show` views → reusable partials (create/edit in modals for simple forms, a page for complex ones). Rooms (`app/Http/Controllers/RoomController.php`) is the reference module.

Business rules that span modules live in two services only:

- `app/Services/BookingService.php` — availability (row-locked), booking, changes, check-in/out, cancellation
- `app/Services/BillingService.php` — invoices (VAT + Tourism Levy, prices tax-inclusive by default), payments, voids

## Pricing

Rates are per room type and meal plan (RO, BB, HB, FB, AI), either per room (optionally by number of adults) or per person with a child rate. Without a rate, the room type's base rate is charged per room. The price agreed at booking is stored on the booking.

Billing settings live under `system.settings.billing`: `prices_include_tax`, `vat_percent`, `tourism_levy_percent`, `child_rate_percent`, `kra_pin`.

## Useful commands

```bash
php artisan hms:sync-invoices          # rebuild every stay's invoice from its rooms/services
php artisan hms:sync-invoices --stay=12
```
