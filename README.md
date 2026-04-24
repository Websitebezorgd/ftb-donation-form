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
| 6 | Mollie integration | Payment flow + webhook status updates | 🔲 Todo |
| 7 | Dashboard | Donation records overview with search, filters, pagination, delete, CSV export | ✅ Done |
| 8 | Translations | i18n + `.pot` file + WPML / TranslatePress compatibility | ✅ Done |
| 9 | Testing | Accessibility + validation (throughout all phases) | 🔄 Ongoing |
| 10 | Security | Nonces, sanitization, escaping, capability checks | ✅ Done |

---

## Current status

Phases 1–5, 7, 8, and 10 are complete. The form collects and stores donations, all admin settings are configurable, submitted donations appear in the WordPress dashboard with search, filters, bulk delete, and CSV export, and the plugin is fully translation-ready with an English translation included.

A full code audit (accessibility, security, bugs, inconsistencies) has been completed and all identified issues resolved. See the open questions section for known remaining topics.

**Next up:** Mollie payment integration — creating payments and handling webhook callbacks to update payment status.

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
- Submitted amount validated against admin-configured preset values
- Frequency validated against recurring setting (only `one_time` accepted when recurring is disabled)
- `post_payment_behavior` validated against allowed values (`message` / `redirect`)

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
│   └── class-ftb-db.php           # Database access layer
├── admin/
│   ├── class-ftb-donation-form-admin.php
│   ├── class-ftb-donations-list-table.php
│   ├── css/ftb-donation-form-admin.css
│   ├── js/ftb-donation-form-admin.js
│   └── partials/
│       ├── ftb-donation-form-admin-display.php
│       └── ftb-donation-form-submissions-display.php
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
    └── ftb-donation-form-en_US.mo    
```

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
- `aria-invalid` on both text inputs and radio groups, updated dynamically by JS
- `aria-required`, `aria-current` throughout
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
ftb-donation-form.pot        ← template, regenerated with WP-CLI after code changes
ftb-donation-form-en_US.po   ← human-editable, translated in Poedit
ftb-donation-form-en_US.mo   ← compiled binary that WordPress reads
```

**Adding a new language:**
1. Open Poedit → File → New from POT/PO file → select `languages/ftb-donation-form.pot`
2. Choose the target language (e.g. `fr_FR`)
3. Translate all strings and save — Poedit writes the `.po` and `.mo`

**Updating the `.pot` after code changes:**

Run from the plugin root (uses Local's PHP binary):
```
"C:\Users\rosit\AppData\Roaming\Local\lightning-services\php-8.2.29+0\bin\win64\php.exe" -c "...php.ini-production" -d "extension_dir=...ext" -d "extension=mbstring" C:\tmp\wp-cli.phar i18n make-pot . languages/ftb-donation-form.pot --domain=ftb-donation-form --exclude=Static,index.html --allow-root
```

Then in Poedit: **Catalogue → Update from POT file** to pick up new strings, save to recompile the `.mo`.

---

## Phase completion rules

A phase is only complete when:

- Code is understandable without AI
- Accessibility is tested (keyboard + screen reader)
- No console errors
- No obvious security issues

---

## Mollie integration (phase 6)

What needs to be built to make payments work:

1. **Mollie PHP SDK** — installed via Composer, or bundled manually in the plugin
2. **Create a payment** — when the form submits successfully, call Mollie's API to create a payment and redirect the donor to Mollie's checkout page
3. **Webhook endpoint** — a WordPress REST API route that Mollie calls when a payment status changes; this updates the record in `wp_ftb_donations`
4. **Return URL** — after payment Mollie sends the donor back to the site; the plugin shows the thank-you message or redirects based on the admin setting
5. **Recurring payments** — for monthly/yearly, Mollie uses mandates (SEPA Direct Debit); the first payment creates the mandate, subsequent ones are charged automatically via the Mollie API

The database layer (`FTB_DB`) and the `mollie_payment_id` / `payment_status` columns are already in place — the groundwork is done.

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

### Dashboard — edit and delete per row
Individual delete is straightforward (row action link). Edit is more work — what should be editable?
The most useful thing would be manually updating the payment status (e.g. marking a bank transfer as paid). Editing donor details is less common and adds significant form complexity.
- [ ] Is updating payment status enough for edit, or do we need to change donor info too?

### Fixed amounts — make optional?
Currently the form always shows three fixed amount buttons.
- [ ] Should fixed amounts be optional, so the form can show only a custom amount input?

### Accessibility — Narrator + radio buttons
`aria-invalid` is now set dynamically on radio inputs when errors appear or clear. Full Narrator testing with the updated behaviour is still outstanding.
- [ ] Retest frequency and amount radio groups with Windows Narrator after the aria-invalid fix (phase 9)

---

## Notes

- Focus is on understanding and maintainability, not speed
- Each phase is tested before the next begins
- Complex features (like payments) are added only after the basics are solid
