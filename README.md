# FTB Donation Form

An accessible WordPress donation plugin built step by step with a strong focus on WCAG 2.2, usability, and maintainability.

---

## Goal

Build a professional donation plugin for WordPress with:

- Accessible (WCAG 2.2) frontend form
- Mollie payment integration
- Flexible admin configuration
- Clean and maintainable codebase

---

## Development approach

This plugin is being rebuilt from scratch to maintain full overview and control.

The development process is split into small, manageable steps:

1. Build a static HTML/CSS/JS version of the form  
2. Ensure accessibility (keyboard, screen readers, validation)  
3. Integrate into WordPress (shortcode, enqueue)  
4. Add backend processing 
5. Expand with admin features and dashboard   
6. Add Mollie integration  
7. Translation support (WPML, TranslatePress, etc.)

---

## Current status

**Current focus:**  
→ Static form (HTML/CSS/JS)  
→ Fix accessibility issues (radio buttons, screen readers, keyboard)

---

## Implementation phases

| # | Phase | Description | Status |
|---|-------|-------------|--------|
| 1 | Plugin base | Main plugin file + shortcode | 🔲 Todo |
| 2 | Static form | HTML/CSS/JS (accessible) | 🔲 In progress |
| 3 | WordPress integration | Render form + enqueue assets | 🔲 Todo |
| 4 | Validation | Client-side (JS) + server-side (PHP) | 🔲 Todo |
| 5 | Admin settings | API key + form configuration | 🔲 Todo |
| 6 | Mollie integration | Payment flow + webhook | 🔲 Todo |
| 7 | Dashboard | Donation records overview | 🔲 Todo |
| 8 | Translations | i18n + `.pot` file | 🔲 Todo |
| 9 | Testing & security | Accessibility + sanitization + nonces | 🔲 Todo |

---

## Planned architecture

ftb-donation-form/
├── ftb-donation-form.php
├── includes/
├── admin/
├── public/
├── assets/
│ ├── css/
│ └── js/
└── languages/


Structure will evolve as the plugin grows.

---

## Accessibility

Accessibility is a core requirement of this plugin:

- Semantic HTML (`fieldset`, `legend`, labels)
- Full keyboard support
- Screen reader support (tested with NVDA and Windows Narrator)
- Clear error handling and focus management
- WCAG 2.2 guidelines

---

## Security (applied throughout)

Security is applied in every phase:

- Nonces for form submissions  
- Sanitizing user input  
- Escaping output  
- Capability checks  
- Secure API handling  

---

## Planned features

- One-time and recurring donations
- Mollie integration
- Configurable form fields
- Privacy consent (GDPR)
- Admin dashboard with export (CSV / Excel / PDF)
- Elementor widget + shortcode

---

## Phase completion rules

A phase is only complete when:

- Code is understandable without AI
- Accessibility is tested (keyboard + screen reader)
- No console errors
- No obvious security issues

## Notes

- Focus is on understanding and maintainability, not speed
- Each part is built and tested before moving to the next step
- Complex features (like payments) are added only after the basics are solid