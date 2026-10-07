# Raging Developers Expense Management

Laravel web application for company expense tracking, company funds, employee running settlements, payment history, and PWA install support.

## Features

- AdminLTE Bootstrap UI for login, admin panel, and employee panel
- Role-based access for `admin` and `employee`
- Active/inactive account protection
- Employee expense CRUD with owner-only access
- Admin employee management
- Admin expense filters and approve/reject actions
- Company fund entry and editing by fund date
- Employee-wise running settlement calculation
- Full or partial payment for pending receivable amount
- Admin and employee payment history
- Mobile-first PWA layout with compact cards, bottom navigation, safe-area spacing, and offline page

## Requirements

- PHP 8.2+
- Composer
- Node.js and npm
- MySQL

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create the database:

```sql
CREATE DATABASE raging_expense_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Set database credentials in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=raging_expense_management
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seeders:

```bash
php artisan migrate --seed
```

Start the app:

```bash
npm run dev
php artisan serve
```

Open `http://127.0.0.1:8000`.

## Default Login Credentials

Admin:

- Email: `admin@ragingdevelopers.com`
- Password: `password`

Employees:

- Email: `employee1@ragingdevelopers.com`
- Password: `password`

Employee seed users are created from `employee1@ragingdevelopers.com` to `employee8@ragingdevelopers.com`.

## Settlement Logic

Settlement is now based on running balance:

- Employee Pending = Total Approved Expenses - Total Payments Received.
- Company Balance = Total Company Funds Added - Total Payments Made.
- Admin can add one fund entry for one month, multiple months, or any date.
- Admin can pay full pending balance of an employee, and after payment employee pending becomes &#8377;0.

Company funds are reduced only when an employee payment is made. Expenses do not directly deduct from company funds.

## Session Lifetime

Login sessions are configured to stay active for 1 year:

- `SESSION_LIFETIME=525600`
- `config/session.php` defaults to `525600` minutes
- `expire_on_close` is disabled so closing the browser does not immediately log the user out
- The login form sends `remember=1`, so Laravel's remember login support is used without removing CSRF or auth middleware

## Mobile PWA UI

The app keeps AdminLTE on desktop and adds mobile-first PWA refinements:

- `public/css/custom-mobile.css` adds compact spacing, safe-area padding, tap-friendly buttons, mobile cards, and bottom navigation
- `resources/views/layouts/partials/mobile-bottom-nav.blade.php` shows role-aware mobile navigation
- Admin and employee dashboards use compact cards on mobile
- Expenses, employees, company funds, settlements, and payments use desktop tables on larger screens and card-style records on mobile
- The manifest uses `display: standalone`, `start_url: /`, portrait orientation, and Raging Developers branding
- The service worker uses cache-first behavior for static assets and network-first behavior for authenticated pages

## PWA Notes

The app includes:

- `public/manifest.json`
- `public/service-worker.js`
- `public/icons/icon.svg`
- `resources/views/offline.blade.php`

The service worker caches basic pages/assets and serves the offline page when a network request fails.
