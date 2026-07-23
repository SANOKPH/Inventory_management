# Inventory Management System

A complete PHP + MySQL inventory management system with authentication,
role-based access control, product/category/supplier CRUD, stock in/out,
stock adjustment, and reporting — built with Bootstrap 5.

## Tech Stack
- **Backend:** PHP 8+ (PDO + prepared statements, sessions)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, Bootstrap 5.3, custom CSS (green brand theme `#1D9E75`),
  Material Design Icons, SweetAlert2 for delete confirmations

## Setup (Laragon / XAMPP)

1. Copy the `inventory_system` folder into your web root
   (e.g. `C:\laragon\www\` or `htdocs/`).
2. Create the database by importing `database.sql` in phpMyAdmin
   (or `mysql -u root -p < database.sql`).
3. Open `config/config.php` and update `DB_HOST`, `DB_USER`, `DB_PASS`
   if they differ from the defaults, and update `BASE_URL` to match
   your folder name if it isn't `/inventory_system/`.
4. Visit `http://localhost/inventory_system/` in your browser.

## Default Login
| Username | Password   | Role  |
|----------|-----------|-------|
| admin    | Admin@123 | Admin |

**Change this password immediately** via Profile → Change Password after first login.

## Registration
- **Public sign-up** (`auth/register.php`, linked from the Login page) is open to
  anyone — new accounts are created with the **Viewer** role and **Active**
  status by default.
- **Admin-managed users** (`users/` — "Manage Users" in the sidebar, Admin only)
  lets an Admin create accounts with any role, and edit an existing user's
  role, status, or reset their password.

## Folder Structure
```
inventory_system/
├── auth/            Login, register, logout, password reset/change, profile
├── middleware/       auth.php (session guard) and role.php (role-based access)
├── dashboard/        Overview stats + recent transactions
├── categories/       Category CRUD
├── products/         Product CRUD (with image upload)
├── suppliers/         Supplier CRUD
├── stock_in/          Receive stock (purchases)
├── stock_out/         Remove stock (sale/damage/expired/lost)
├── adjustment/        Correct stock counts
├── reports/           8 report types (current stock, low stock, valuation, etc.)
├── includes/          Shared header/sidebar/topbar/footer
├── assets/            css/js
├── uploads/            Product images & profile pictures
├── config/config.php Database connection
└── database.sql      Full schema + seed admin user
```

## Roles & Permissions
| Role       | Access                                                        |
|------------|-----------------------------------------------------------------|
| Admin      | Full access, including user management                          |
| Manager    | Dashboard, Products, Categories, Suppliers, Stock, Reports       |
| Inventory  | Products, Categories, Stock In/Out/Adjustment (no suppliers)     |
| Cashier    | Dashboard + Reports only (extend as needed for a sales module)   |
| Viewer     | Dashboard + Reports (read-only)                                  |

## Security Notes
- Passwords hashed with `password_hash()` / verified with `password_verify()`.
- All queries use PDO prepared statements (SQL injection safe).
- Session ID regenerated on login; auto-logout after 30 minutes idle
  (`SESSION_TIMEOUT` in `config/config.php`).
- Role checks are enforced server-side via `middleware/role.php` on every
  restricted page — never rely on hiding sidebar links alone.
- Enable HTTPS in production and update file upload limits in `php.ini`
  as needed for product/profile images.

## Notes
- Stock quantity is never edited directly on the Product form — it can only
  change through **Stock In**, **Stock Out**, or **Stock Adjustment**, so
  every change is tracked with a reason and timestamp (see the "Product
  Movement" report for a full audit trail).
- The password-reset flow works in demo mode (it shows the reset link on
  screen instead of emailing it) — wire up `mail()` or an SMTP library
  in `auth/forgot_password.php` for production use.
