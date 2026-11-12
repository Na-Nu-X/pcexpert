# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

PC Expert is a Slovak-language website for a PC services business. It has user accounts, including Google login, reviews, a contact form, an admin panel and paid service orders through Stripe. It is plain PHP rendered on the server, with no framework. It runs under XAMPP at `/opt/lampp/htdocs/pcexpert` and is served at `http://pcexpert.local` through an Apache vhost (see README.md for setup). The UI text, routes and many error messages are in Slovak.

## Commands

- `./run_dev.sh` opens two gnome-terminals running the watchers below.
- `npm run watch` runs `tsc --watch` and compiles `src/ts/src/**` → `src/ts/dist/**` (rootDir and outDir are set in tsconfig.json).
- `npx sass --watch .:.` compiles every `*.scss` in place into `*.css` next to it (for example `src/css/styles.scss` → `src/css/styles.css`).
- `composer install` installs the PHP dependencies (Stripe, PHPMailer, Google API client, phpdotenv) into `vendor/`.
- `sudo /opt/lampp/lampp restart` restarts Apache/XAMPP.
- Stripe webhooks in local development: `stripe listen --forward-to http://pcexpert.local/src/php/api/stripe_webhook.php`, then put the secret it prints into `STRIPE_WEBHOOK_SECRET` in `.env`.

There is no test suite and no linter. `npx tsc --noEmit` type-checks the TypeScript under strict settings: `noUncheckedIndexedAccess`, `exactOptionalPropertyTypes`, `noPropertyAccessFromIndexSignature` (so use `el.dataset["x"]`, not `el.dataset.x`), and no unused locals or parameters.

## Architecture

### Routing (`.htaccess`)
- The site root and clean URLs map to `src/php/<name>.php`, so `/login` → `src/php/login.php`.
- Slovak SEO aliases are listed explicitly, for example `/prihlasenie` → login.php, `/administracia` → admin.php and `/objednavka-uspesna` → success_order.php. Add an alias here whenever you add a page.
- 404 → `src/php/404.php`. `src/assets/.htaccess` denies all direct web access to `src/assets/`.

### PHP pages (`src/php/*.php`)
Each page is a self-contained script, with logic at the top and HTML below:
1. `require` dependencies using **relative paths** (`../assets/functions.php`, `../classes/*.php`, `../../vendor/autoload.php`). These resolve against the script's directory, so they break if a file moves.
2. Load `.env` with `Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2))` and read values via `$_ENV[...]`.
3. Instantiate helper classes (`new Database()`, `new User()`, …) and call `$database_object->connectionDB()` to get a `PDO`.
4. `session_start()`, generate a CSRF token into `$_SESSION["csrf_token"]`, read the `theme` cookie (light/dark, applied as a CSS class) and auto-login from the `remember_me_token` cookie.
5. Handle POST forms in the same file, many of them gated by Google reCAPTCHA v3. Flash messages go through `$_SESSION["green_report_message"]` (and similar keys).
6. Render HTML that includes `/src/css/*.css`, compiled `/src/ts/dist/**.js` as `type="module"`, and `../assets/footer.php`.

`index.php` (~1700 lines) is the homepage: services, the order modals, the contact form and reviews.

### AJAX fragment endpoints
`all_reviews.php`, `all_users.php`, `all_errors.php` and `error_details.php` are not full pages. They take POST data (`offset`, `user_id`, `id`) and return **HTML fragments**, which the TypeScript fetches by relative URL and injects for "load more" pagination. They load only the classes they need.

### Data layer (`src/classes/`)
- These are stateless classes whose methods take the `PDO $connection` as their first argument, for example `$user_object->getUserInformation($connection, $id, "first_name")`. All queries use prepared statements.
- `Database::connectionDB()` connects to **PostgreSQL** (`127.0.0.1:5433`, db `pcexpert`). The README's MySQL instructions are outdated, and the MySQL code is commented out.
- `src/assets/functions.php` holds the global helpers `redirectURL()`, `isLoggedIn()`, `deleteSession()` and `errorLogSave()`. `errorLogSave()` writes both to the `errors` DB table and to a log file, and the admin panel reads the DB table.

### Orders / Stripe flow
1. The service modals in index.php POST to `create_checkout_session.php` with `pay`, `service` (`computer_building` | `computer_maintenance` | `computer_cleaning`; prices are hardcoded there), customer fields and up to 5 attachments.
2. That script creates a Stripe Checkout Session, then calls `Order::createOrder()` using the session id as `stripe_intent_id`. It stores attachments in `content/uploads/attachments/` (gitignored) via `storeOrderAttachments()`, then redirects to Stripe.
3. `src/php/api/stripe_webhook.php` handles `checkout.session.completed` → `markAsPaid` and `checkout.session.expired` → `markAsCancelled`.
4. Stripe redirects back to `/objednavka-uspesna?session_id=...`. `success_order.php` then shows the order and its attachments via `getOrderInformation()`.

### Frontend
- **TypeScript** (`src/ts/src/`): `components/` holds widgets shared across pages (navigation_bar, preloader, cookie_policy, theme_selection, attachment upload), `pages/<page>/components/` holds page-specific scripts, and `utils/` holds helpers. Each file is a native ES module, wrapped in a `DOMContentLoaded` listener, that does vanilla DOM work with no framework or bundler. Imports between modules **must use the `.js` extension** (`import { x } from "../utils/foo.js"`), because the browser loads the tsc output directly.
- **SCSS** (`src/css/`): `styles.scss` is global, alongside `forms.scss`, `admin.scss` and `success_order.scss`, with shared `partials/_variables.scss` and `_mixins.scss`. `*.css` is gitignored, but some compiled CSS files are already tracked.
- `src/js/` holds plain JS and vendored lightbox. Icons come from the Font Awesome kit, which loads from a CDN.

### Uploads / static content
`content/images/` holds tracked site images. `content/uploads/` is gitignored and contains `<user_id>/` profile pictures and `attachments/` for orders.

## Conventions
- PHP: typed method signatures, a docblock on each function (`@param`, `@return`), and comments in Title Case (`// Gets The User ID`). Use snake_case for variables and camelCase for methods.
- TS: explicit type annotations on nearly every `const`, snake_case variable names, no semicolons, and the same Title Case trailing comments.
- Git commit messages are written in Title Case, past tense ("Added …", "Completed …").
