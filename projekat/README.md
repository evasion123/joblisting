# Job Listings (PHP + MySQL + AJAX)

Minimal, working job board where guests can browse listings and registered users can apply.

## Setup (XAMPP)

1. Start **Apache** and **MySQL** in XAMPP.
2. Open **phpMyAdmin** → Import `database.sql` to create schema + sample data.
3. Copy the folder `job_listings_site` into your `htdocs` (e.g., `C:\xampp\htdocs\job_listings_site`).
4. Visit: http://localhost/job_listings_site/
5. Login with demo account: **demo@example.com** / **demo123**, or register a new user.

## Files
- `config.php` — DB credentials.
- `db.php` — PDO connection.
- `init.php` — starts session and includes DB.
- `index.php` — UI shell; loads jobs via AJAX and shows Apply buttons.
- `api/listings.php` — returns jobs as JSON.
- `api/apply.php` — accepts job applications (requires login).
- `assets/styles.css` — styles.
- `assets/main.js` — front-end logic.
- `login.php`, `register.php`, `logout.php` — auth pages.
- `database.sql` — schema + seed data.

## Notes
- Applications are unique per user/job; duplicate apply attempts return an error.
- To change DB password/host, edit `config.php`.
- This is intentionally simple: no admin or company dashboards. You can extend it later.
