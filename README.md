# ftb-donation-form
WordPress donation plugin with a WCAG 2.2-compliant form, Mollie payment integration, admin customization UI (no-code), payment dashboard with multi-format export, and Elementor/translator plugin support.

## Plan: Build ftb-donation-form WordPress Plugin

### TL;DR

Build a professional WordPress donation plugin with a WCAG 2.2-compliant form, Mollie payment integration, admin customization UI (no-code), payment dashboard with multi-format export, and Elementor/translator plugin support.

### Implementation Phases (8 phases, ~4-6 weeks)

**Phase 1: Project Setup** — Create plugin structure, Mollie SDK integration, custom post type, activation hooks
**Phase 2: Database & Admin** — Custom donation table, admin settings page (Mollie key, field toggles, amount options), dashboard infrastructure
**Phase 3: Frontend Form** — WCAG 2.2 compliant form with dynamic field rendering based on admin config, accessible error handling
**Phase 4: Mollie Integration** — Payment service class, webhook handling, payment processing, email confirmations
**Phase 5: Admin Dashboard** — Donation records view, filtering/search, CSV/PDF/Excel export via meta box UI
**Phase 6: Elementor & Shortcode** — `[ftb_donation_form]` shortcode + Elementor widget
**Phase 7: Translations** — Wrap strings with `__()` functions, generate `.pot` file, test WPML/TranslatePress compatibility
**Phase 8: Testing & Docs** — Manual testing, accessibility audit, documentation

### Key Architecture Decisions

- **Single global form** (designed for easy multi-form expansion later)
- **Custom database table** for donation records (queried separately from WordPress posts)
- **Admin settings page** for field configuration (no-code via checkboxes and text inputs)
- **Mollie API key** encrypted in `wp_options` with nonce-protected form submission
- **Fixed field labels** + translatable via WPML/TranslatePress (not admin-customizable)
- **Webhook-based payment confirmation** (more reliable than redirects)
- **Configurable post-payment behavior** (redirect, message, or email)

### Form Fields (as specified)

- **Donation frequency**: One-time, Weekly, Monthly, Yearly (radio buttons)
- **Amount**: €5, €10, €25, or custom amount (radio buttons with conditional input)
- **Donor info**: Full name, email, phone, street, house number, postal code, city
- **Privacy**: Link to privacy statement + GDPR consent checkbox
- All field labels translatable via WPML/TranslatePress

### Critical Files Structure

```
ftb-donation-form/
├── ftb-donation-form.php          (Main plugin)
├── includes/                      (Core logic: Mollie service, DB ORM)
├── admin/                         (Settings, dashboard, field config UI)
├── public/                        (Form rendering, submission handler, validation)
├── assets/                        (CSS, JS, images)
├── elementor/                     (Elementor widget)
└── languages/                     (Translation template)
```

### What's Included

✅ WCAG 2.2 compliant form  
✅ Mollie integration with automatic API key storage + webhook handling  
✅ Admin customization (field toggles, amount options, payment behavior) — no code needed  
✅ Donation records dashboard with multi-format export (CSV, PDF, Excel)  
✅ One-time + recurring payments (weekly, monthly, yearly)  
✅ Email confirmations for donors  
✅ Elementor widget + shortcode `[ftb_donation_form]`  
✅ WPML/TranslatePress compatible  

### What's Excluded (MVP scope)

❌ Multi-form/campaign management (designed for easy future addition)  
❌ Advanced Elementor styling customization  
❌ Subscription management UI  
❌ Tax invoicing/reporting  
❌ Automatic webhook configuration in Mollie

---
## File structure 

ftb-donation-form/
├── ftb-donation-form.php          (Main plugin file with header and initialization)
├── includes/                      (Core logic)
│   ├── class-ftb-donation-form.php
│   ├── class-ftb-donation-form-loader.php
│   └── class-ftb-donation-form-i18n.php
├── admin/                         (Admin functionality)
│   ├── class-ftb-donation-form-admin.php
│   ├── partials/
│   │   └── ftb-donation-form-admin-display.php
│   ├── css/
│   │   └── ftb-donation-form-admin.css
│   └── js/
│       └── ftb-donation-form-admin.js
├── public/                        (Frontend functionality)
│   ├── class-ftb-donation-form-public.php
│   ├── partials/
│   │   └── ftb-donation-form-public-display.php
│   ├── css/
│   │   └── ftb-donation-form-public.css
│   └── js/
│       └── ftb-donation-form-public.js
├── assets/                        (Shared assets - currently empty)
├── elementor/                     (Elementor integration - to be implemented)
└── languages/                     (Translations)
    └── ftb-donation-form.pot