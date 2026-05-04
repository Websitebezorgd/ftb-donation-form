# FTB Donation Form

An accessible WordPress donation plugin built with a strong focus on WCAG 2.2, usability, and maintainability.

**Live demo:** [GitHub Pages](https://websitebezorgd.github.io/ftb-donation-form/)

---

## Goal

Build a professional donation plugin for WordPress with:

- Accessible (WCAG 2.2) frontend form
- Mollie payment integration
- Flexible admin configuration
- Clean and maintainable codebase

---

## Development approach

This plugin is built from scratch to maintain full overview and control. Each phase is completed and tested before the next one starts.

---

## Implementation phases

| # | Phase | Description | Status |
|---|-------|-------------|--------|
| 1 | Plugin base | Main plugin file, activation hook, DB table, shortcode | ✅ Done |
| 2 | Static form | Accessible HTML/CSS/JS form + GitHub Pages demo | ✅ Done |
| 3 | WordPress integration | Shortcode rendering + asset enqueue | ✅ Done |
| 4 | Validation | Client-side (JS) + server-side (PHP) + nonces | ✅ Done |
| 5 | Admin settings | API key, form options, fields, privacy, post-payment | ✅ Done |
| 6 | Mollie integration | Payment flow + webhook status updates | 🔄 Ongoing |
| 7 | Dashboard | Donation records overview with search, filters, pagination, delete, CSV export, row actions | ✅ Done |
| 8 | Translations | i18n + `.pot` file + WPML / TranslatePress compatibility | ✅ Done |
| 9 | Testing | Accessibility + validation (throughout all phases) | 🔄 Ongoing |
| 10 | Security | Nonces, sanitization, escaping, capability checks | ✅ Done |

---

## Current status

Phases 1–5, 7, 8, and 10 are complete. Phase 6 (Mollie) is in progress.

The one-time payment flow is fully built and tested locally: the form redirects donors to Mollie's checkout page, and the thank-you message or redirect is shown on return. The webhook endpoint is built and secured but requires a live HTTPS server for full end-to-end testing — Mollie cannot reach a local development environment. Recurring payments (monthly/yearly) are not yet built.

The dashboard now includes individual row delete and a payment status edit page per donation. A full code audit (accessibility, security, bugs) has been completed. All high and medium severity issues have been resolved. Known lower-priority issues remain — see the open questions section.

**Next up:** Test the webhook flow on a staging server with HTTPS, then build recurring payment support.

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

### Mollie payments
- One-time payment flow: form redirects donor to Mollie checkout, returns to thank-you or redirect page
- Webhook endpoint at `/wp-json/ftb/v1/webhook` — Mollie calls this when payment status changes; updates `payment_status` in the database
- API key validated against Mollie on save — shows an error notice if the key is invalid
- Webhook URL omitted on local dev environments (Mollie requires HTTPS; local sites are HTTP)
- REST namespace restricted to POST only — no data accessible via GET

### Admin settings
- **Mollie:** API key + test mode toggle
- **Titel:** customisable form heading
- **Frequentie:** enable/disable recurring payments — includes a reminder to activate SEPA Direct Debit in Mollie
- **Bedragopties:** up to three fixed amounts + custom amount toggle + configurable minimum
- **Formuliervelden:** optional field toggles (phone, address fields)
- **Privacyverklaring:** privacy statement URL — includes an AVG/GDPR reminder when left empty
- **Na betaling:** show a thank-you message or redirect to a page

### Donation dashboard
- All submitted donations listed with: name, email, phone, address, amount, frequency, status, date
- Sortable columns, search box, and status filter tabs (all / pending / paid / failed / cancelled)
- Payment status badges
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
- Thank-you message sanitised as plain text (`sanitize_textarea_field`) — consistent with `esc_html()` output
- Mollie API key validated against Mollie on save via `methods->allActive()`
- Webhook re-fetches payment from Mollie before updating status — never trusts raw POST body
- Webhook checks donation exists in DB before making any Mollie API call
- REST namespace `/ftb/v1` restricted to POST — any GET request returns 403
- Webhook URL only passed to Mollie when HTTPS; omitted on `.local` / `localhost` to prevent Mollie rejecting the payment

---

## Architecture

```
ftb-donation-form/
├── ftb-donation-form.php          # Main plugin file, activation hook
├── index.html                     # GitHub Pages demo
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
│   └── partials/
│       ├── ftb-donation-form-admin-display.php
│       ├── ftb-donation-form-submissions-display.php
│       └── ftb-donation-form-edit-status-display.php
├── public/
│   ├── class-ftb-donation-form-public.php
│   ├── css/ftb-donation-form-public.css
│   ├── js/ftb-donation-form-public.js
│   └── partials/
│       └── ftb-donation-form-public-display.php
├── Static/                        # Assets for GitHub Pages demo
│   ├── style.css
│   └── script.js
└── languages/
    ├── ftb-donation-form.pot
    ├── ftb-donation-form-en_US.po
    ├── ftb-donation-form-en_US.mo
    └── ftb-donation-form-en_US.l10n.php
```

> `.distignore` in the plugin root lists files excluded from the WordPress.org distribution zip (dotfiles, `Static/`, `index.html`, `README.md`). Use `wp dist-archive .` to build a clean zip.

---

## Shortcode

```
[ftb_donation_form]
```

Optionally override the title:

```
[ftb_donation_form title="Steun ons"]
```

---

## Accessibility

Accessibility is a core requirement, not an afterthought:

- Semantic HTML (`fieldset`, `legend`, associated labels)
- Full keyboard support and visible focus indicators
- Screen reader support (tested with NVDA and Windows Narrator)
- Error summary with focus management on validation failure
- `aria-invalid` on radio groups, text inputs, and checkboxes — set on validation error, cleared when the user corrects the field
- `aria-required`, `aria-current` throughout
- Custom amount container uses `hidden` attribute (not CSS-only) so screen readers cannot reach a hidden input
- Admin conditional fields use `hidden` attribute when not visible — inputs inside are inaccessible to screen readers when hidden
- Step 2 intro paragraph receives focus on navigation so screen readers hear the context before the fields
- Labels are plain text only; no links or interactive elements embedded in label text (NL Design System)
- WCAG 2.2 guidelines

---

## Translations (phase 8)

All PHP strings are wrapped in `__()` / `esc_html_e()` / `esc_attr_e()` with the text domain `ftb-donation-form`. JavaScript error messages are passed from PHP via `wp_localize_script` — no separate JS translation file needed.

**How it works:**
- Site locale `nl_NL` → no `.mo` file needed → Dutch strings render from code
- Site locale `en_US` → WordPress loads `ftb-donation-form-en_US.mo` → English shown
- With WPML or TranslatePress the locale switches automatically — no special handling needed

**Files per language** (in `/languages`):
```
ftb-donation-form.pot             ← template, regenerated with WP-CLI after code changes
ftb-donation-form-en_US.po        ← human-editable, translated in Poedit
ftb-donation-form-en_US.mo        ← compiled binary that WordPress reads
ftb-donation-form-en_US.l10n.php  ← PHP cache used by WordPress 6.5+ for faster loading
```

**Adding a new language:**
1. Open Poedit → File → New from POT/PO file → select `languages/ftb-donation-form.pot`
2. Choose the target language (e.g. `fr_FR`)
3. Translate all strings and save — Poedit writes the `.po` and `.mo`

**Updating translations after code changes — checklist:**

1. Regenerate `.pot` (run from plugin root in Local's site shell):
```
wp i18n make-pot . languages/ftb-donation-form.pot --domain=ftb-donation-form --exclude=vendor
```
2. In Poedit: open `ftb-donation-form-en_US.po` → **Catalogue → Update from POT file** → translate new strings → Save
3. Recompile `.mo` and `.l10n.php`:
```
wp i18n make-mo languages/ftb-donation-form-en_US.po
wp i18n make-php languages/ftb-donation-form-en_US.po languages/
```
4. Add ABSPATH check on line 2 of the regenerated `.l10n.php`:
```php
if ( ! defined( 'ABSPATH' ) ) exit;
```

---

## Phase completion rules

A phase is only complete when:

- Code is understandable without AI
- Accessibility is tested (keyboard + screen reader)
- No console errors
- No obvious security issues

---

## Mollie integration (phase 6)

### Built
1. **Mollie PHP SDK** — installed via Composer (`mollie/mollie-api-php ^3.10`), autoloaded in `ftb-donation-form.php`
2. **Create a payment** — on successful form submission, `FTB_Mollie_Service::create_payment()` calls Mollie and redirects the donor to the checkout page; the Mollie payment ID is stored in `wp_ftb_donations`
3. **Webhook endpoint** — `POST /wp-json/ftb/v1/webhook`; Mollie calls this when status changes; the handler re-fetches the payment from Mollie and updates `payment_status` in the database
4. **Return URL** — after payment, Mollie sends the donor back; the plugin shows the thank-you message or redirects based on the admin setting (`ftb_post_payment_behavior`)
5. **API key validation** — the key is tested against Mollie when saved in admin settings

### Still to do
6. **Webhook end-to-end test** — requires a live server with HTTPS; Mollie cannot call a local development URL
7. **Recurring payments** — monthly/yearly donations use Mollie Subscriptions (SEPA Direct Debit); the first payment creates a mandate, subsequent charges happen automatically via the API

### Requirements before going live
- SSL certificate on the hosting (HTTPS required for Mollie webhooks)
- Live Mollie API key entered in settings with Test mode disabled

---

## WordPress plugin repository

Requirements to get accepted on wordpress.org:

1. **License** — `GPL-2.0-or-later` declared in the main plugin header and a `LICENSE` file
2. **Readme.txt** — a separate `readme.txt` in wordpress.org format (different from `README.md`), with `Tested up to`, `Requires at least`, `Stable tag`, and a changelog
3. **No external calls without disclosure** — bundling the Mollie SDK or documenting the API call is required
4. **No minified JS without source** — source JS files must be included in the package
5. **Translation-ready** — a `.pot` file in `/languages` and all strings wrapped in `__()` / `esc_html_e()` (already done)
6. **Sanitize / escape / nonce** — already done, but reviewers check this strictly
7. **Prefixed everything** — all functions, classes, and options use `ftb_` (already done)
8. **No bundled Composer autoloader with dev dependencies** — only production dependencies in the plugin package

Outstanding before submitting: `readme.txt`, `LICENSE` file, and a cleanly bundled Mollie SDK.

---

## Open questions

### Accessibility — Narrator + radio buttons
`aria-invalid` is now set dynamically on radio inputs when errors appear or clear. Full Narrator testing with the updated behaviour is still outstanding.
- [ ] Retest frequency and amount radio groups with Windows Narrator after the aria-invalid fix (phase 9)


---

## Notes

- Focus is on understanding and maintainability, not speed
- Each phase is tested before the next begins
- Complex features (like payments) are added only after the basics are solid
