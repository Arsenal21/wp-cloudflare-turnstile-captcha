=== CF Turnstile for WP ===
Contributors: mra13, Tips and Tricks HQ
Tags: turnstile, captcha, cloudflare, spam-protection, security
Donate link: https://www.tipsandtricks-hq.com/
Requires at least: 6.5
Requires PHP: 8.0
Tested up to: 6.8
Stable tag: 1.0.0
License: GPLv2 or later

A lightweight plugin that adds Cloudflare Turnstile CAPTCHA to core WordPress forms and selected third‑party plugins to block spam and bot attacks.

== Description ==

CloudFlare Turnstile for WP lets you drop-in Cloudflare's privacy-focused, no-CAPTCHA challenge on the most common attack surfaces of a WordPress site:

* **Core forms** – login, registration, password reset, and comments.
* **Accept Stripe Payments** – protect checkout and payment pop-ups.
* **Simple Download Monitor** – secure download buttons and squeeze forms.

Just add your Turnstile *Site Key* and *Secret Key*, choose the forms you want to protect, and you’re done. No more subjecting your users to image puzzles or accessibility headaches.

Turnstile can generate multiple types of non-intrusive challenges to verify users are human, all without showing visitors a puzzle.

### Highlights
* Zero-friction, user-friendly bot protection.
* A free reCAPTCHA alternative for WordPress.
* Works even when visitors are behind ad-blockers or privacy extensions.
* Granular toggles to enable/disable on individual forms.
* Debug logging feature.
* Fully translatable and developer-friendly with action/filter hooks.
* Road-map for upcoming integrations with other popular plugins.

== Installation ==
1. Upload the plugin ZIP via **Plugins → Add New → Upload Plugin**, or install it directly from the WordPress.org repository.
2. Activate **CloudFlare Turnstile for WP** via the **Plugins** menu.
3. Navigate to **Settings → Turnstile**.
4. Enter your **Site Key** and **Secret Key** from the Cloudflare dashboard.
5. Check the boxes for the forms and integrations you wish to protect.
6. Save changes and test a form to confirm the Turnstile widget appears.

== Frequently Asked Questions ==

= Where do I get a Site Key and Secret Key? =
Sign in to your Cloudflare account, add a Turnstile application, and copy the credentials provided.

= Does this slow down my site? =
No. The Turnstile script is tiny and loaded from Cloudflare's global edge network. It adds a negligible footprint.

= Can I style or reposition the widget? =
Yes – you can choose a theme and widget size in the settings menu.

= I only need it on comments – is that possible? =
Absolutely. Toggle off any forms you don't wish to protect.

== Screenshots ==
1. **Settings page** – add keys and choose forms.
2. **Login form** secured by Turnstile.
3. **Registration form** secured by Turnstile.
4. **Checkout form** inside Accept Stripe Payments.

== Changelog ==

= 1.0.0 =
* Initial release.
* Adds Turnstile protection to login, registration, password reset, and comment forms.
* Integrates with Accept Stripe Payments and Simple Download Monitor.
* Provides granular enable/disable controls per form.

== Upgrade Notice ==
None.
