# FTB Donation Form

A professional WordPress donation plugin with a WCAG 2.2-compliant form, Mollie payment integration, no-code admin customization UI, payment dashboard with multi-format export, and Elementor/translator plugin support.

---

## Overview

FTB Donation Form is a WordPress plugin for collecting one-time and recurring donations via Mollie. It includes a WCAG 2.2-compliant frontend form, a no-code admin settings page, a donation records dashboard with export options, and support for Elementor and translation plugins.

---

## Implementation Phases

| # | Phase | Description | Status |
|---|-------|-------------|--------|
| 1 | **Project Setup** | Plugin structure, Mollie API via WP HTTP, custom DB table, activation hooks | ✅ Done |
| 2 | **Database & Admin** | DB class, admin settings page (Mollie key, field toggles, amount options, post-payment behavior, privacy URL) | ✅ Done |
| 3 | **Frontend Form** | WCAG 2.2 compliant form, dynamic fields based on admin config, accessible error handling | 🔲 Next |
| 4 | **Mollie Integration** | Payment service class, webhook handling, payment processing, email confirmations | 🔲 Todo |
| 5 | **Admin Dashboard** | Donation records view, filtering/search, CSV/PDF/Excel export | 🔲 Todo |
| 6 | **Elementor & Shortcode** | `[ftb_donation_form]` shortcode + Elementor widget | 🔲 Todo |
| 7 | **Translations** | Wrap strings with `__()`, generate `.pot` file, test WPML/TranslatePress | 🔲 Todo |
| 8 | **Testing & Docs** | Manual testing, accessibility audit, documentation | 🔲 Todo |

---

## Key Architecture Decisions

- **Single global form** — designed for easy multi-form expansion later
- **Custom database table** — donation records queried separately from WordPress posts
- **Admin settings page** — field configuration via checkboxes and text inputs (no code needed)
- **Mollie API key** — stored in `wp_options`, form submission nonce-protected
- **Dutch as default language** — all strings written in Dutch, wrapped in `__()` so WPML/TranslatePress can translate them to any language
- **Fixed field labels** — translatable via WPML/TranslatePress, not admin-customizable
- **Privacy policy URL** — configurable in admin; linked from the GDPR consent field in the form
- **Configurable post-payment behavior** — admin can choose between a redirect URL or a custom thank-you message shown after successful payment
- **Webhook-based payment confirmation** — more reliable than redirect-based confirmation

---

## Admin Settings

| Section | Options |
|---------|---------|
| Mollie | API key |
| Form fields | Toggle optional fields (phone, street, house number, postal code, city) |
| Amount options | Up to three preset amounts; custom amount always available |
| Privacy policy | URL linked from the GDPR consent field |
| Post-payment | Redirect URL or thank-you message |

---

## Form Fields

| Field | Type | Notes |
|-------|------|-------|
| Donation frequency | Radio buttons | One-time, Weekly, Monthly, Yearly |
| Amount | Radio buttons + text input | Up to 3 preset amounts (admin-configured), or custom |
| Full name | Text | Required |
| Email | Email | Required |
| Phone | Tel | Optional (admin toggle) |
| Street | Text | Optional (admin toggle) |
| House number | Text | Optional (admin toggle) |
| Postal code | Text | Optional (admin toggle) |
| City | Text | Optional (admin toggle) |
| GDPR consent | Checkbox | Required, links to privacy policy URL |

All field labels are translatable via any standard WordPress translation plugin (WPML, TranslatePress, Polylang, Loco Translate, qTranslate-XT, etc.).

---

## What's Included

- WCAG 2.2 compliant form
- Mollie integration with API key storage and webhook handling
- Admin customization — field toggles, amount options, post-payment behavior, privacy URL (no code needed)
- Donation records dashboard with multi-format export (CSV, PDF, Excel)
- One-time and recurring payments (weekly, monthly, yearly)
- Email confirmations for donors
- Elementor widget and `[ftb_donation_form]` shortcode
- WPML/TranslatePress compatible

## What's Excluded (MVP scope)

- Multi-form/campaign management (designed for easy future addition)
- Advanced Elementor styling customization
- Subscription management UI
- Tax invoicing/reporting
- Automatic webhook configuration in Mollie

---

## Things to consider:

- Safety check (escape, sanatize, nonces, rights etc.) !! 

---

## File Structure

```
ftb-donation-form/
├── ftb-donation-form.php               Main plugin file (header, constants, activation)
├── includes/                           Core logic
│   ├── class-ftb-donation-form.php     Main orchestrator class
│   ├── class-ftb-donation-form-loader.php  Hook management
│   ├── class-ftb-donation-form-i18n.php    Internationalization
│   └── class-ftb-db.php                Database access layer (donations table)
├── admin/                              Admin area
│   ├── class-ftb-donation-form-admin.php   Admin class (menu, settings, enqueue)
│   ├── partials/
│   │   └── ftb-donation-form-admin-display.php  Settings page template
│   ├── css/
│   │   └── ftb-donation-form-admin.css
│   └── js/
│       └── ftb-donation-form-admin.js
├── public/                             Frontend
│   ├── class-ftb-donation-form-public.php  Public class (shortcode, enqueue)
│   ├── partials/
│   │   └── ftb-donation-form-public-display.php  Form template
│   ├── css/
│   │   └── ftb-donation-form-public.css
│   └── js/
│       └── ftb-donation-form-public.js
└── languages/
    └── ftb-donation-form.pot           Translation template
```

---

## Getting Started

### Requirements

- PHP 7.4+
- WordPress 5.0+
- A Mollie account with an API key

### Installation

1. Upload the plugin folder to `wp-content/plugins/ftb-donation-form/`
2. Activate the plugin in **Plugins > Installed Plugins**
3. Go to **Donation Form** in the sidebar
4. Paste your Mollie API key and save
