# FTB Donation Form

An accessible WordPress donation plugin built with a strong focus on WCAG 2.2, usability, and maintainability.

---

## Goal

Build a professional donation plugin for WordPress with:

- Accessible (WCAG 2.2) frontend form
- Mollie payment integration
- Flexible admin configuration
- Clean and maintainable codebase

---

## Development approach

Built from scratch to maintain full overview and control. Each phase is completed and tested before the next one starts.

---

## Implementation phases

| # | Phase | Description | Status |
|---|-------|-------------|--------|
| 1 | Plugin base | Main plugin file, activation hook, DB table, shortcode | ✅ Done |
| 2 | Static form | Accessible HTML/CSS/JS form | ✅ Done |
| 3 | WordPress integration | Shortcode rendering + asset enqueue | ✅ Done |
| 4 | Validation | Client-side (JS) + server-side (PHP) + nonces | ✅ Done |
| 5 | Admin settings | API key, form options, fields, privacy, post-payment | ✅ Done |
| 6 | Mollie integration | Payment flow + webhook status updates | 🔄 Ongoing |
| 7 | Dashboard | Donation records overview with search, filters, pagination, delete, CSV export, row actions | ✅ Done |
| 8 | Translations | i18n + `.pot` file + WPML / TranslatePress compatibility | ✅ Done |
| 9 | Testing | Accessibility + validation (throughout all phases) | 🔄 Ongoing |
| 10 | Security | Nonces, sanitization, escaping, capability checks | ✅ Done |
| 11 | Email notifications | Donor confirmation email + admin notification on new donation, both toggleable | ✅ Done |
| 12 | Branding | For The Better logo, favicon, and brand colours in the WordPress admin — shared header/footer partials across all admin pages | ✅ Done |
| 13 | Polish | Admin mobile responsiveness, themeable frontend colour tokens, translation cleanup | ✅ Done |
| 14 | Style variants | Layout variants (Card / Flat / Minimal) + primary colour picker selectable in admin with live preview; PHP outputs the chosen class and colour token on the shortcode. When Elementor is active, global colours are shown as swatches; colour token uses `var(--e-global-color-primary, #c42e31)` as automatic fallback | ✅ Done |

---

## Current status

Phases 1–5, 7, 8, 10–14 are complete. Phase 6 (Mollie) is in progress.

The one-time payment flow is fully built and tested on the live test site. Email notifications (donor confirmation + admin notification) are confirmed working. The webhook endpoint is built and secured; full webhook testing requires a live HTTPS server.

Recurring payments are also built: the plugin creates a Mollie customer and first payment to establish the SEPA mandate; the webhook then creates a subscription after the first payment is confirmed; Mollie handles subsequent charges automatically.

**Next up:** Test the full payment flow (one-time + recurring) on SiteGround. Retest accessibility (Narrator + radio groups).

---

## Features

### Frontend form
- Multi-step form (donation details → personal details)
- One-time, monthly, and yearly frequency options (recurring can be toggled off in settings)
- Up to three fixed amount options + optional custom amount with configurable minimum
- Optional fields: phone number, street, house number, postal code, city
- GDPR consent checkbox with link to privacy statement
- Client-side and server-side validation with accessible error summary
- Full keyboard navigation, screen reader support, WCAG 2.2
- Colour tokens overridable per site via CSS custom properties on `.ftb-donation-form`; style variant classes (`--style-card`, `--style-flat`, `--style-minimal`) applied automatically based on admin setting
- Adapts to container width — works inside Elementor columns and any page builder

### Mollie payments
- One-time payment flow: form redirects donor to Mollie checkout, returns to thank-you or redirect page
- Webhook endpoint at `/wp-json/ftb/v1/webhook` — Mollie calls this when payment status changes
- On return from Mollie, payment status is re-fetched directly from the API if the webhook has not yet arrived (handles local dev and race conditions)
- Cancelled, failed, or expired payments show a status-specific message on the form instead of silently showing the form again
- Mollie statuses mapped to internal values: `open` → `pending`, `canceled` → `cancelled`; `expired` fully supported
- API key validated against Mollie on save — shows an error notice if the key is invalid
- Webhook URL omitted on local dev environments (Mollie requires HTTPS; local sites are HTTP)
- REST namespace restricted to POST only — no data accessible via GET
- Missing API key shows an inline error in the form instead of breaking the page (Elementor compatible)

### Admin settings
- **Mollie:** API key + test mode toggle
- **Titel:** customisable form heading
- **Frequentie:** enable/disable recurring payments — includes a reminder to activate SEPA Direct Debit in Mollie
- **Bedragopties:** up to three fixed amounts + custom amount toggle + configurable minimum
- **Formuliervelden:** optional field toggles (phone, address fields)
- **Privacyverklaring:** privacy statement URL — includes an AVG/GDPR reminder when left empty; when a URL is entered, a ready-to-copy privacy statement text is shown (fields + Mollie + AVG rights, with sender email if configured)
- **Na betaling:** show a thank-you message or redirect to a page
- **E-mailnotificaties:** admin notification toggle + donor confirmation email with editable subject, body, and live preview
- **Stijl:** layout variant (Card / Flat / Minimal) + primary colour picker with live preview; Elementor global colours shown as swatches when Elementor is active

### Admin UI
- Shared header (logo + dashicon) and footer (For The Better credit) across all admin pages via PHP partials
- Mobile responsive: two-column settings grid stacks to single column at ≤ 782px
- CSV export button inline with the page title on the donations page

### Donation dashboard
- All submitted donations listed with: name, email, phone, address, amount, frequency, status, date
- Sortable columns, search box, and status filter tabs (all / pending / paid / failed / cancelled / expired)
- Payment status badges with per-status colours
- Individual delete per row (nonce-protected)
- Payment status edit per row (dropdown, nonce-protected)
- Bulk delete
- CSV export (UTF-8 with BOM for Excel)

### Translations
- All strings wrapped in `__()` / `esc_html_e()` / `esc_attr_e()`
- `.pot` file included in `/languages`
- English (`en_US`) translation included
- Compatible with WPML and TranslatePress

### Security
- Nonces on every form submission
- All input sanitised, all output escaped
- Capability checks (`ftb_manage_settings`) on every admin page
- Prepared statements for all database queries
- Submitted amount validated against admin-configured preset values; stored as integer cents to avoid float precision issues
- Frequency validated against recurring setting (only `one_time` accepted when recurring is disabled)
- `post_payment_behavior` validated against allowed values (`message` / `redirect`)
- All user-submitted text fields capped with `mb_substr()` to match database `varchar` column lengths
- Mollie API key validated against Mollie on save via `methods->allActive()`
- Webhook re-fetches payment from Mollie before updating status — never trusts raw POST body
- Webhook checks donation exists in DB before making any Mollie API call
- REST namespace `/ftb/v1` restricted to POST — any GET request returns 403
- Webhook URL only passed to Mollie when HTTPS; omitted on `.local` / `localhost`

---

## Architecture

```
ftb-donation-form/
├── ftb-donation-form.php          # Main plugin file, activation hook
├── includes/
│   ├── class-ftb-donation-form.php
│   ├── class-ftb-donation-form-loader.php
│   ├── class-ftb-donation-form-i18n.php
│   ├── class-ftb-db.php           # Database access layer
│   └── class-ftb-mollie-service.php  # Mollie SDK wrapper
├── admin/
│   ├── class-ftb-donation-form-admin.php
│   ├── class-ftb-donations-list-table.php
│   ├── css/ftb-donation-form-admin.css
│   ├── js/ftb-donation-form-admin.js
│   ├── images/
│   │   ├── for-the-better-logo.png
│   │   └── for-the-better-favicon.png
│   └── partials/
│       ├── ftb-donation-form-admin-display.php
│       ├── ftb-donation-form-submissions-display.php
│       ├── ftb-donation-form-edit-status-display.php
│       ├── ftb-donation-form-admin-header.php
│       └── ftb-donation-form-admin-footer.php
├── public/
│   ├── class-ftb-donation-form-public.php
│   ├── css/ftb-donation-form-public.css
│   ├── js/ftb-donation-form-public.js
│   └── partials/
│       └── ftb-donation-form-public-display.php
└── languages/
    ├── ftb-donation-form.pot
    ├── ftb-donation-form-en_US.po
    ├── ftb-donation-form-en_US.mo
    └── ftb-donation-form-en_US.l10n.php
```

> `.distignore` lists files excluded from the distribution zip (dotfiles, `README.md`). Use the GitHub Actions release workflow to build a clean zip — see `.github/workflows/release.yml`.

---

## Shortcode

```
[ftb_donation_form]
```

---

## Accessibility

Accessibility is a core requirement, not an afterthought:

- Semantic HTML (`fieldset`, `legend`, associated labels)
- Full keyboard support and visible focus indicators
- Screen reader support (tested with NVDA and Windows Narrator)
- Error summary with focus management on validation failure
- `aria-invalid` on radio groups, text inputs, and checkboxes — set on validation error, cleared when corrected
- `aria-required`, `aria-current` throughout
- Custom amount container uses `hidden` attribute (not CSS-only) so screen readers cannot reach a hidden input
- Admin conditional fields use `hidden` attribute when not visible
- Step 2 intro paragraph receives focus on navigation so screen readers hear the context before the fields
- Labels are plain text only; no links or interactive elements embedded in label text (NL Design System)
- WCAG 2.2 guidelines

---

## Translations

All PHP strings use `__()` / `esc_html_e()` / `esc_attr_e()` with the text domain `ftb-donation-form`. JavaScript error messages are passed from PHP via `wp_localize_script`.

**How it works:**
- Site locale `nl_NL` → Dutch strings render from code, no translation file needed
- Site locale `en_US` → WordPress loads `ftb-donation-form-en_US.l10n.php` (or `.mo` as fallback)
- WPML / TranslatePress: locale switches automatically — no special handling needed

**Updating translations after code changes:**

1. Regenerate `.pot` (run from plugin root):
```
wp i18n make-pot . languages/ftb-donation-form.pot --domain=ftb-donation-form --exclude=vendor
```
2. In Poedit: open `ftb-donation-form-en_US.po` → **Catalogus → Bijwerken vanuit POT-bestand** → translate new strings → Save
3. Remove obsolete strings: **Vertalingen → Verouderde vertalingen verwijderen** → Save
4. Regenerate `.l10n.php` (Poedit does not always update this file on save):
```
wp i18n make-php languages/ftb-donation-form-en_US.po
```
5. Open the generated `languages/ftb-donation-form-en_US.l10n.php` and add the ABSPATH guard directly after `<?php`:
```php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
```

**Adding a new language:**
1. Poedit → File → New from POT/PO file → select `languages/ftb-donation-form.pot`
2. Choose the target language (e.g. `fr_FR`)
3. Translate all strings and save — Poedit writes the `.po`, `.mo`, and `.l10n.php`

---

## Mollie integration

### Built
1. **Mollie PHP SDK** — installed via Composer (`mollie/mollie-api-php ^3.10`), autoloaded in `ftb-donation-form.php`
2. **Create a payment** — on successful form submission, `FTB_Mollie_Service::create_payment()` calls Mollie and redirects the donor to the checkout page; the Mollie payment ID is stored in `wp_ftb_donations`
3. **Webhook endpoint** — `POST /wp-json/ftb/v1/webhook`; Mollie calls this when status changes; the handler re-fetches the payment from Mollie and updates `payment_status` in the database
4. **Return URL** — after payment, Mollie sends the donor back; the plugin shows the thank-you message or redirects based on `ftb_post_payment_behavior`
5. **API key validation** — tested against Mollie on save
6. **Recurring payments** — Mollie customer created on form submit; first payment uses `sequenceType: first` to establish the SEPA mandate; webhook creates the subscription after the first payment is paid; subsequent charges are matched via `subscriptionId`

### Requirements before going live
- SSL certificate on the hosting (HTTPS required for Mollie webhooks)
- Live Mollie API key entered in settings with Test mode disabled
- SEPA Direct Debit enabled in the Mollie dashboard (for recurring payments)

---

## WordPress plugin repository

Requirements to get accepted on wordpress.org:

1. **License** — `GPL-2.0-or-later` declared in the main plugin header and `LICENSE` file ✅
2. **Readme.txt** — `readme.txt` in wordpress.org format with `Tested up to`, `Requires at least`, `Stable tag`, and a changelog ✅
3. **No external calls without disclosure** — Mollie SDK bundled and documented
4. **No minified JS without source** — source JS files are included
5. **Translation-ready** — `.pot` file in `/languages`, all strings wrapped ✅
6. **Sanitize / escape / nonce** — done ✅
7. **Prefixed everything** — all functions, classes, and options use `ftb_` ✅
8. **No bundled Composer autoloader with dev dependencies** — only production dependencies in the package

---

## Adding a new admin page

1. Add a private property: `private $your_hook = '';`
2. Register the page in `add_plugin_admin_menu()` and store the return value: `$this->your_hook = add_submenu_page( ... );`
3. Add the hook to `is_plugin_page()` so CSS/JS load on it
4. Add a display method that calls `render_admin_page()` with a title and content callback
5. Create a content-only partial in `admin/partials/` — no wrapper, header, or footer needed

`render_admin_page()` handles the wrap div, shared header, `<hr>`, and shared footer automatically.

---

## Phase completion rules

A phase is only complete when:

- Code is understandable without AI
- Accessibility is tested (keyboard + screen reader)
- No console errors
- No obvious security issues

---

## Open questions / what's left

### Live testing on SiteGround — payments + emails
All webhook-dependent features require a live HTTPS server.

**Payment flow:**
- [x] One-time payment: form → Mollie checkout → webhook → thank-you message/redirect
- [x] Recurring payment: mandate creation, subscription creation, subsequent charge webhook handling

**Email notifications:**
- [x] Donor confirmation: confirmed working on SiteGround test site
- [x] Admin notification: confirmed working on SiteGround test site
- [x] Sender name shows site title in From header
- [x] Empty body sends details block only (no blank intro line)

### Accessibility — Narrator + radio buttons
- [x] Frequency and amount radio groups work correctly with Windows Narrator
- [x] Step indicator announced correctly — `aria-current="step"` on the `<li>` is sufficient; aria-live caused double announcements so was not added

### Plugin updates on client sites
How to deliver updates to clients once the plugin is installed. Options to explore: WordPress.org repository, a private update server (e.g. WP Update Server), or a GitHub-based updater hook. To be decided.

### Uninstall behaviour
`uninstall.php` currently deletes all plugin data (donations table + all options + user capabilities) whenever the plugin is removed via Dashboard → Plugins. Whether this is the right default — or whether users should be able to choose what gets deleted — still needs to be decided.

