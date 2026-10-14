=== For The Better donatieformulier ===
Contributors: forthebetter
Tags: donation, mollie, fundraising, wcag, recurring
Requires at least: 5.9
Tested up to: 7.1
Stable tag: 1.0.2
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WCAG 2.2 compliant donation form with Mollie integration, admin customization, and one-time or recurring payments.

== Description ==

FTB Donation Form is a WordPress plugin for collecting one-time and recurring donations via Mollie. It includes a WCAG 2.2-compliant frontend form, a no-code admin settings page, a donation records dashboard with export options, and support for Elementor and translation plugins.

= Features =
* WCAG 2.2 compliant form
* Mollie integration with API key storage and webhook handling
* Admin customization — field toggles, amount options, post-payment behavior, privacy URL
* Donation records dashboard with multi-format export (CSV, PDF, Excel)
* One-time and recurring payments (monthly, yearly)
* Email confirmations for donors
* Elementor widget and [ftb_donation_form] shortcode
* WPML/TranslatePress compatible

== Installation ==

1. Upload the plugin folder to `wp-content/plugins/ftb-donation-form/`
2. Activate the plugin in **Plugins > Installed Plugins**
3. Go to **Donation Form** in the sidebar
4. Paste your Mollie API key and save

== Requirements ==

* PHP 7.4 or higher
* WordPress 5.9 or higher
* A Mollie account with an API key

== Changelog ==

= 1.0.2 =
* Email address for notifications and sender is only shown when an email is switched on, with a clearer label and a notice showing the WordPress admin address used when it is left empty
* Accessibility: error summary links go to the right field (also on the other step), focus moves to server-side errors and to the message after returning from Mollie, and the custom amount field announces its error
* Accessibility: focus ring on amount and frequency options also after a mouse click or screen reader activation, and a visible marker after "Next" with the keyboard
* Focus no longer changes the shape of buttons and fields set by the theme (e.g. Elementor rounded buttons)

= 1.0.1 =
* Webhook: emails are sent only once per payment and a recurring donation can no longer create two subscriptions
* Form submissions are handled before the page is rendered, so the redirect to Mollie works on every server and theme
* Setting to delete all data when the plugin is deleted (off by default)
* Tested up to WordPress 7.1

= 1.0.0 =
* Initial release
