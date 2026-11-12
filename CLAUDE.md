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
4. `session_start()`, generate a CSRF token into `$_SESSION["csrf_token"]`, read the `theme` cookie (light/dark, applied **only** as a class on `<body>`) and auto-login from the `remember_me_token` cookie.
5. Handle POST forms in the same file, many of them gated by Google reCAPTCHA v3. Flash messages go through `$_SESSION["green_report_message"]` (and similar keys).
6. Render HTML that includes `/src/css/*.css`, compiled `/src/ts/dist/**.js` as `type="module"`, and the shared partials (see below).

`index.php` is the homepage: hero, services, about/FAQ, reviews and contact. The services (cards and checkout dialogs) are driven by the `$all_services` array at the end of its PHP block. That array holds the title, price, icon, image, card features, badge and checkout questions file, so add or edit a service there.

### Shared partials (`src/assets/partials/`)
- `flash_messages.php`, `cookie_policy.php` and `navigation.php` are included by every full page. The navigation takes optional `$navigation_links` (an array of label/href pairs) and `$is_homepage`. When `$user_object` and `$connection` are set, it shows the account and admin links.
- `checkout_dialog.php` is the 3-step order wizard (contact → delivery → details). It is configured by a `$checkout` array (one `$all_services` entry) and includes `checkout_details_<service>.php` for the service-specific questions.
- `review_card.php` and `admin_review_card.php`, `admin_user_row.php`, `admin_error_row.php` are the row/card templates. Each is shared between a full page and its AJAX fragment endpoint, so the markup stays identical. Change them in one place.
- `src/assets/footer.php` is the footer content, placed inside `<footer>`.

### AJAX fragment endpoints
`all_reviews.php`, `all_users.php`, `all_errors.php` and `error_details.php` are not full pages. They take POST data (`offset`, `user_id`, `error_id`) and return **HTML fragments** (built from the partials above), which the TypeScript fetches by relative URL and injects for "load more" pagination. They load only the classes they need.

### Data layer (`src/classes/`)
- These are stateless classes whose methods take the `PDO $connection` as their first argument, for example `$user_object->getUserInformation($connection, $id, "first_name")`. All queries use prepared statements.
- `Database::connectionDB()` connects to **PostgreSQL** using the `POSTGRES_HOST/PORT/DB/USER/PASSWORD` values from `.env`, loading `.env` itself if the page hasn't (see `.env.example`). Never hard-code credentials.
- `src/assets/functions.php` holds the global helpers `redirectURL()`, `isLoggedIn()`, `deleteSession()`, `errorLogSave()`, `redactSensitiveData()`, `isAdmin()` and `requireAdmin()`. `errorLogSave()` writes both to the `errors` DB table and to a log file, and the admin panel reads the DB table. Before storing `$_POST` it passes it through `redactSensitiveData()` (password, token, csrf, recaptcha, reset-code and similar keys); extend that pattern if you add new sensitive fields.
- **Authorization:** every admin action and admin-only endpoint must call `requireAdmin($connection, "<action>")` before doing anything. It re-reads the role from the DB, logs the attempt and exits with 403. CSRF checks alone are not enough, because every visitor's session has a token. `admin.php` uses `$is_admin = isAdmin($connection)` for the view.

### Orders / Stripe flow
1. Each service card in index.php is a `<form>` holding a `checkout_dialog.php` dialog. It POSTs to `create_checkout_session.php` with `pay`, `service` (`computer_building` | `computer_maintenance` | `computer_cleaning`), customer and shipping fields (Packeta), the service answers and up to 5 `<service>_attachment[]` files. The authoritative prices are hardcoded in `create_checkout_session.php`. Keep the display prices in `$all_services` in sync with them. Keep the form field names unchanged too, because the server reads them directly.
2. That script creates a Stripe Checkout Session, then calls `Order::createOrder()` using the session id as `stripe_intent_id`. It stores attachments in `content/uploads/attachments/` (gitignored) via `storeOrderAttachments()`, then redirects to Stripe.
3. `src/php/api/stripe_webhook.php` handles `checkout.session.completed` → `markAsPaid` and `checkout.session.expired` → `markAsCancelled`.
4. Stripe redirects back to `/objednavka-uspesna?session_id=...`. `success_order.php` then shows the order and its attachments via `getOrderInformation()`.

### Frontend
- **TypeScript** (`src/ts/src/`): `components/` holds widgets shared across pages, `pages/<page>/components/` holds page-specific scripts, and `utils/` holds helpers. The key shared modules are:
  - `navigation_bar` (mobile menu, scrolled state; it also initializes the theme toggle from `theme_selection`, which switches the `<body>` class and cookie without reloading)
  - `checkout_wizard` (the `CheckoutWizard` class, one per service form)
  - `range_field` and `attachment`
  - `utils/packeta` (typed Packeta widget wrapper)

  `CheckoutWizard` validates declaratively: native constraints (`required`, required radio groups), `[data-required-group]` (at least one checkbox checked) and `input[type=hidden][data-required]` inside the selected shipping method. New questions usually need only markup. Each file is a native ES module, wrapped in a `DOMContentLoaded` listener, that does vanilla DOM work with no framework or bundler. Imports between modules **must use the `.js` extension** (`import { x } from "../utils/foo.js"`), because the browser loads the tsc output directly.
- **SCSS** (`src/css/`): `styles.scss` is global (base, utilities such as `.container`, `.section`, `.section_heading`, `.btn`/`.btn_secondary`/`.btn_ghost`/`.btn_danger`/`.btn_sm`/`.btn_lg`, `.card`, `.badge`, plus navigation, toasts, cookie bar and footer). It is the only file that imports `partials/_theme.scss`. Alongside it are `index.scss` (homepage, imports `partials/_checkout.scss`), `forms.scss` (auth/account/404 cards), `admin.scss` and `success_order.scss`. `*.css` is gitignored, but some compiled CSS files are already tracked.
- **Design system:** colors, spacing, radii, shadows and fonts are CSS custom properties defined in `partials/_theme.scss`, with separate light and dark values on `body.light` / `body.dark`. Always use `var(--color-*)`, `var(--space-*)` etc. in components; never hard-code colors or add per-element `light`/`dark` classes. Raw palette values and breakpoints (`sm` 480, `md` 768, `lg` 1024, `xl` 1280) live in `partials/_variables.scss`. Use the `up("md")` / `down("lg")` mixins for media queries and `button()`, `card()`, `form_input`, `heading`, `eyebrow`, `container` from `partials/_mixins.scss`. The `lg` breakpoint is mirrored as `DESKTOP_BREAKPOINT` in `navigation_bar.ts`.
- `src/js/` holds plain JS and vendored lightbox. Icons come from the Font Awesome kit, which loads from a CDN and is domain-restricted, so icons don't render on other origins such as `localhost:<port>`. Fonts are Inter (body) and Plus Jakarta Sans (headings) from Google Fonts.

### Uploads / static content
`content/images/` holds tracked site images. `content/uploads/` is gitignored and contains `<user_id>/` profile pictures and `attachments/` for orders.

## Conventions
- PHP: typed method signatures, a docblock on each function (`@param`, `@return`), and comments in Title Case (`// Gets The User ID`). Use snake_case for variables and camelCase for methods.
- TS: explicit type annotations on nearly every `const`, snake_case variable names, no semicolons, and the same Title Case trailing comments.
- Git commit messages are written in Title Case, past tense ("Added …", "Completed …").
