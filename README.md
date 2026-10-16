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
- Colour tokens overridable per site via CSS custom properties on `.ftb-donation-form`; style variant classes (`--style-card`, `--style-flat`, `--style-minimal`) applied automatically based on admin setting. Card/Flat visual rules are scoped with `:not(.ftb-donation-form--style-minimal)`; Minimal lets the theme style headings, labels, fields, links and buttons and only falls back to the plugin style via `@layer ftb-donation-form-fallback` (any unlayered theme rule wins). On block themes, Minimal buttons also get `wp-element-button` so the theme.json button style applies (not on classic themes, where WordPress would add its default button style). Layout, focus and error styles always apply
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

### Accessibility — frontend form (axe-core + keyboard, 2026-10-14)
- [x] Error summary links for frequency and amount pointed to non-existent IDs — now point to the radio / custom amount input; clicking a link opens the right step and moves focus
- [x] Server-side errors and the message after returning from Mollie now receive focus on page load (`role="alert"` is not announced on load)
- [x] Errors on both steps: the form opens on step 1 instead of step 2
- [x] `aria-expanded` removed from the "Anders" radio (not allowed on radios)
- [x] Custom amount input gets `aria-invalid` and `aria-describedby` for its error
- [x] Error summary links at least 24px high (WCAG 2.5.8)
- [x] Removed meaningless `aria-describedby` on the wrapper and the incorrect `autocomplete="address-line2"` on house number
- [x] Manual test with NVDA + Chrome (2026-10-14)
- [ ] Manual test with Narrator + Edge
- [ ] Mobile test with VoiceOver (iOS) / TalkBack (Android)

### Accessibility — admin dashboard
Found via WAVE + Narrator:
- [x] **Lege link (WAVE)** — Iris color picker drag-handles verborgen via `aria-hidden`, `tabindex="-1"` en `role="presentation"` na `wpColorPicker()` init
- [x] **Switchers (Narrator)** — `role="switch"` toegevoegd, aangekondigd als "schakelaar ingeschakeld/uitgeschakeld"
- [x] **Dropdown (Narrator)** — werkt correct, geen aanpassing nodig

### Code review by colleague (2026-10-16)
Verdict: not releasable until 1 and 2 are fixed (done). Planned order: 5 → 6 → 7 → 4 (short fix) → small items.

**Findings:**
- [x] **1. Mollie API key readable in the page source** — key never written into the HTML any more; masked display (`live_••••Ab3x`), empty field keeps the key, separate "Verwijderen" button with confirmation, invalid keys are not saved (previous key kept), Mollie unreachable → saved with a warning. Users with plugin access can still add/change/delete the key (by design: clients usually have the editor role)
- [x] **2. Reactivating gave all editors access again** — `FTB_Access::apply()` (`includes/class-ftb-access.php`) is used both on activation and when saving "Toegang en beheer". New installs default to "Alleen beheerders" (listed first); existing sites keep their choice. Chosen editors who no longer have the editor role lose access
- [x] **3. Formulas in the CSV export** — `csv_safe()` prefixes donor values starting with `= + - @`, tab or line break with an apostrophe (visible in Excel; documented in HANDLEIDING). Database unchanged
- [ ] **4. Duplicate thank-you emails for recurring charges** — short fix: remember the Mollie payment ID per charge and only mail when it is new and becomes paid. Structural fix: one row per charge (see "Each subscription charge as its own record" below). Can only be tested for real once SEPA Direct Debit is approved
- [x] **5. Form fails after ~a day on cached pages** — an expired nonce now gives a form error (input kept, opens on step 2) instead of `wp_die()`; the form JS fetches a fresh nonce on page load via `admin-ajax.php?action=ftb_refresh_nonce` (never cached). Caching tip in HANDLEIDING
- [ ] **6. External thank-you page redirects to wp-admin** — `wp_safe_redirect()` without the redirect URL's host in `allowed_redirect_hosts`
- [ ] **7. Double-click on "Doneer nu"** — two donations and two Mollie payments; disable the button after the first click
- [ ] **8. Orphan "pending" donations** — same as the Robustness item below
- [ ] **9. No maximum amount** — Mollie refuses e.g. € 99.999.999 with a generic error
- [ ] **10. Refresh after payment** — return token is deleted on the first visit, a refresh shows an empty form
- [ ] **11. Test mode toggle does nothing** — remove it or warn when it doesn't match the key (test_/live_)
- [ ] **12. Separate recipient for admin notifications** — discuss first (we chose one field with fallback to the admin email)
- [ ] **13. Uninstall leftovers** — `ftb_form_style`, `ftb_form_primary_color`, `ftb_return_*` transients
- [ ] **14. CSS/JS loaded on every page** — only enqueue when the shortcode is on the page
- [ ] **15. GDPR tools** — hook into WordPress personal data export/erase; retention period
- [ ] **16. Release workflow** — validate the version input and pass it via an env variable
- [ ] **17. Duplicate code** — same as the Cleanup item below
- [ ] **18. Orphan docblock** in `class-ftb-mollie-service.php` — same as the Cleanup item below
- [ ] **19. Automated tests** — same as the Quality item below

**WordPress.org repository (later):** source strings are Dutch — English is strongly recommended as source language for translate.wordpress.org; readme must be in English.

### Code review findings (2026-10-12)
Fix the first group before the next live test on SiteGround, otherwise the tests have to be repeated.

**Before the live test — payments:**
- [x] **Duplicate subscriptions** — the webhook now claims the donation (`FTB_DB::claim_subscription_creation()`) before creating the subscription and passes an idempotency key to Mollie. If creating the subscription fails, the claim is released and the webhook returns `500`, so Mollie retries
- [x] **Duplicate emails** — emails are only sent by the call that actually changes the status to `paid` (`FTB_DB::transition_payment_status()`), whether that is the webhook or the return flow. **Exception:** subscription charges still mail on every `paid` webhook call, because all charges share one row — solved by the next-but-one item
- [x] **Redirect from inside the shortcode** — form submissions and returns from Mollie are now handled in `handle_form_request()` on `template_redirect`, before any output. The shortcode only renders the form with the outcome. If the shortcode is rendered outside the main request (e.g. a page builder preview), it falls back to processing on render
- [ ] **Each subscription charge as its own record** — a new charge overwrites the status of the original donation; one failed charge marks the whole donation `failed` and earlier payments are not visible. Store each charge as a separate row linked to the subscription
- [ ] **Cancelling subscriptions** — there is no way to stop a subscription (admin or donor). When the plugin is deleted with data, the table is dropped but charges in Mollie continue. At minimum: a "Cancel subscription" action in the dashboard

**Robustness:**
- [ ] **Spam protection** — no honeypot or rate limit; every submission creates a DB row and, for recurring, a Mollie customer
- [ ] **Orphan `pending` rows** — the donation is inserted before the API key / SEPA checks; the return value of `insert_donation()` is not checked
- [ ] **Hide recurring options when Mollie can't do recurring** — when SEPA Direct Debit is not active in Mollie (e.g. rejected), donors can still choose monthly/yearly and only get "Terugkerende betalingen zijn momenteel niet beschikbaar" after filling in the whole form. Hide the frequency choice in that case and show a warning in the admin settings. Cache the result of `is_recurring_available()` (e.g. a transient refreshed when the API key or settings are saved) instead of calling Mollie on every page load
- [ ] **Failed subscription creation is invisible** — only logged when `WP_DEBUG` is on. Show an admin notice or retry

**Cleanup:**
- [ ] `vendor/composer/*` and `vendor/autoload.php` are tracked in git despite `vendor/` in `.gitignore` — remove with `git rm -r --cached vendor`
- [ ] Duplicate orphan docblock in `includes/class-ftb-mollie-service.php` (above `create_customer()`)
- [ ] `readme.txt` mentions PDF/Excel export and an Elementor widget — only CSV export and the shortcode exist
- [ ] Webhook URL + local-dev check and the Mollie status mapping are duplicated — extract shared helpers

**Quality:**
- [ ] PHPUnit tests for the webhook logic (status mapping, subscription creation, when emails are sent)
- [ ] Run PHPCS and PHPStan in GitHub Actions on every push

### Plugin Check (WordPress 7.1.3)
Run on the development folder: 3 errors, 4 warnings.
- [x] **`outdated_tested_upto_header`** — `Tested up to` raised from 7.0 to 7.1 in `readme.txt` and the plugin header
- [ ] **Re-run Plugin Check on the release zip** — install the zip from the GitHub release workflow on a test site and check that installation; only what remains there needs fixing

Not an issue — these files are listed in `.distignore` and are not part of the release zip:
- `hidden_files` — `.editorconfig`, `.distignore`, `.gitignore`
- `github_directory` — `.github`
- `unexpected_markdown_file` — `HANDLEIDING.md`
- `missing_direct_file_access_protection` — `phpstan-bootstrap.php`. Do **not** add an `ABSPATH` check here: PHPStan runs without WordPress, so `exit` would stop the analysis

### Plugin updates on client sites
How to deliver updates to clients once the plugin is installed. Options to explore: WordPress.org repository, a private update server (e.g. WP Update Server), or a GitHub-based updater hook. To be decided.

### Uninstall behaviour
- [x] Deactivation preserves all data (only removes capabilities)
- [x] Deletion: data is kept by default. Administrators can opt in via the **Delete all data on uninstall** toggle under *Access and management* in the settings (option `ftb_delete_data_on_uninstall`); `uninstall.php` only drops the table and options when it is `1`. A warning is shown while the toggle is on.

