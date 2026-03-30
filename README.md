# FTB Donation Form

A professional WordPress donation plugin with a WCAG 2.2-compliant form, Mollie payment integration, no-code admin customization UI, payment dashboard with multi-format export, and Elementor/translator plugin support.

---

## Overview

Build a professional WordPress donation plugin with a WCAG 2.2-compliant form, Mollie payment integration, admin customization UI (no-code), payment dashboard with multi-format export, and Elementor/translator plugin support.

---

## Implementation Phases

| # | Phase | Description | Status |
|---|-------|-------------|--------|
| 1 | **Project Setup** | Plugin structure, Mollie API via WP HTTP, custom DB table, activation hooks | ✅ Done |
| 2 | **Database & Admin** | DB ORM class, admin settings page (Mollie key, field toggles, amount options) | 🔲 Next |
| 3 | **Frontend Form** | WCAG 2.2 compliant form, dynamic fields based on admin config, accessible error handling | 🔲 Todo |
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
- **Webhook-based payment confirmation** — more reliable than redirect-based confirmation
- **Configurable post-payment behavior** — redirect, message, or email

---

## Form Fields

| Field | Type | Notes |
|-------|------|-------|
| Donation frequency | Radio buttons | One-time, Weekly, Monthly, Yearly |
| Amount | Radio buttons + text input | €5, €10, €25, or custom amount |
| Full name | Text | Required |
| Email | Email | Required |
| Phone | Tel | Optional (admin toggle) |
| Street | Text | Optional (admin toggle) |
| House number | Text | Optional (admin toggle) |
| Postal code | Text | Optional (admin toggle) |
| City | Text | Optional (admin toggle) |
| GDPR consent | Checkbox | Required, links to privacy statement |

All field labels are translatable via WPML/TranslatePress.

---

## What's Included

- WCAG 2.2 compliant form
- Mollie integration with API key storage and webhook handling
- Admin customization — field toggles, amount options, payment behavior (no code needed)
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

## File Structure

```
ftb-donation-form/
├── ftb-donation-form.php               Main plugin file (header, constants, activation)
├── includes/                           Core logic
│   ├── class-ftb-donation-form.php     Main orchestrator class
│   ├── class-ftb-donation-form-loader.php  Hook management
│   └── class-ftb-donation-form-i18n.php    Internationalization
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
├── elementor/                          Elementor widget (Phase 6)
├── assets/                             Shared assets
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


