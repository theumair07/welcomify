=== Welcomify - Custom Welcome Dashboard ===
Contributors: theumair07
Tags: dashboard, welcome panel, elementor, bricks, page builder
Requires at least: 5.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.5.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Replace the WordPress dashboard welcome panel with your own design, per user role. Works with Bricks, Elementor, Gutenberg and other builders.

== Description ==

**Welcomify** replaces the default WordPress welcome panel with a design you build yourself. Show a different design to each user role, including custom roles.

= How it works =

How you create the design depends on your page builder.

**Bricks Builder and Elementor: use a template**

1. Create a template: **Bricks > Templates** or **Elementor > Templates**.
2. Design your welcome content and publish the template.
3. Go to **Welcomify > Settings**. For each role, pick the template from **Bricks Templates** or **Elementor Templates**.
4. Click **Save Changes**.

Templates work for every role, also for roles that cannot open the builder. Visitors cannot open them.

Pages built with Bricks or Elementor are not listed. For these two builders, always use a template.

**Gutenberg, Kadence, Divi, Beaver Builder, Breakdance and other editors: use a page**

1. Go to **Pages > Add New** and design the page with your editor.
2. Set the page **Visibility** to **Private**, so visitors cannot open it. (Published pages also work.)
3. Go to **Welcomify > Settings**. For each role, pick the page from **Pages**.
4. Click **Save Changes**.

**Welcomify > Getting Started** shows these steps inside WordPress, with links to the right screens.

= Features =

* **A design per role** - A different page or template for every role, including custom roles.
* **Any page builder** - Bricks and Elementor templates, or a page built with Gutenberg, Kadence, Divi, Beaver Builder, Breakdance and more.
* **Dismissible** - Per role: let users close the panel. They can bring it back from Screen Options.
* **Hide other widgets** - Per role: remove the other dashboard widgets, so only your design is shown.
* **Clean display** - The site header, footer, sidebar and page title are hidden inside the panel.
* **Secure** - Your template is loaded with a short-lived link that only works for the logged-in user and that template.
* **Multisite** - Use pages and templates from the main site on every subsite.
* **Lightweight and translation ready**

= Use cases =

* A branded onboarding screen for new users
* Announcements for specific user roles
* Quick-start guides for content creators
* A client welcome screen with links and contact details

== Installation ==

1. Install Welcomify from **Plugins > Add New**, or upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin.
3. Open **Welcomify > Getting Started** and follow the steps for your page builder.
4. Open **Welcomify > Settings**, choose a page or template for each role and save.

== Frequently Asked Questions ==

= Does it work with custom user roles? =

Yes. Every role on your site gets its own card in the settings, including roles added by other plugins.

= Do I need Elementor or Bricks? =

No. You can use any editor, including the default WordPress Block Editor (Gutenberg).

= Why don't I see my Elementor or Bricks page in the list? =

For Elementor and Bricks, Welcomify uses templates, not pages. Create a template under Elementor > Templates or Bricks > Templates and it appears in the list.

= Can visitors see my welcome page? =

Not if you follow the steps: Bricks and Elementor templates cannot be opened by visitors, and other pages should be set to Private. Welcomify loads them in the dashboard with a short-lived link that only works for the logged-in user.

= Can I use draft or password protected pages? =

Use Private pages. Drafts are only shown to users who are allowed to edit them (usually Administrators and Editors). Password protected pages show the password form inside the dashboard.

= Can users close the welcome panel? =

Yes, if you switch on **Dismissible** for their role. They can show it again under **Screen Options > Welcome** on the dashboard.

= What does "Hide other widgets" do? =

It removes the other dashboard widgets (Activity, Quick Draft, widgets from other plugins and so on) for that role, so only your design is shown. It stays that way when the user closes the panel. It is on by default.

= Will this work with my theme? =

Yes. The design is shown in a frame inside the dashboard, without your theme's header, footer and sidebar.

= Is multisite supported? =

Yes. Subsites can use pages and templates from the main site, and you can hide the settings from subsites.

= What happens if I delete the assigned page or template? =

The dashboard shows the default WordPress welcome panel again until you choose a new one.

== Screenshots ==

1. Your own dashboard per role - an Administrator dashboard built with Bricks.
2. Settings - one card for every user role, with Dismissible and Hide other widgets.
3. Choose a Bricks template, an Elementor template or any page.
4. An Editor dashboard built with Elementor.
5. Custom roles included - a client portal for a custom role, built with Bricks.
6. Getting Started - the steps for each page builder.

== Changelog ==

= 1.5.1 =
* Fixed: The welcome panel is now shown by default to users of roles with full admin rights, such as a copy of the Administrator role.

= 1.5.0 =
* New: Getting Started page with steps for each page builder.
* New: Settings page with a card for every user role, a save bar and an unsaved-changes warning.
* New: "Hide other widgets" option per role.
* New: Users of every role can close the welcome panel and show it again from Screen Options.
* Changed: New design for all Welcomify admin screens.
* Changed: New screenshots.
* Fixed: Custom roles saw the home page instead of the selected Bricks or Elementor template.
* Fixed: Private pages now work as the welcome page for every role.
* Fixed: Elementor's "Default Kit" is no longer offered as a template.
* Fixed: The "Settings" link on the Plugins page.
* Improved: Templates are loaded with a short-lived, signed link for the logged-in user only.
* Improved: Keyboard and screen reader support on the settings page.
* Improved: All plugin data is removed on uninstall.
* Removed: White Label page and Pro upgrade screen.
* Tested with WordPress 7.1.

= 1.4.1 =
* Updated the translation template.

= 1.4.0 =
* New: Dropdown with keyboard navigation and grouped Pages / Elementor Templates / Bricks Templates.
* New: Divi, Beaver Builder and Breakdance page support.
* Changed: "Dashboard" submenu renamed to "Settings".
* Improved: Builder labels only show when the builder is active.
* Improved: Deleted pages fall back to the default WordPress welcome panel.

= 1.3.3 =
* New: Filter `umy_wdw_settings_title` to change the settings title.

= 1.3.2 =
* Fixed: Menu icon not showing.
* Fixed: Blank Template Library page.

= 1.3.1 =
* Fixed: Missing menu icon on some admin themes.

= 1.3.0 =
* New: Top-level Welcomify menu.
* New: Detection for Bricks, Divi, Beaver Builder, Oxygen and Breakdance.

= 1.2.0 =
* Fixed: White space flicker when content inside the panel opens or closes.
* Added: Direct file access protection in uninstall.php.

= 1.1.0 =
* Fixed: Links inside the panel opened inside the frame instead of the main window.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.5.1 =
Shows the welcome panel by default to roles with full admin rights.

= 1.5.0 =
Fixes custom roles seeing the home page instead of their template. Adds a new Settings page, a Getting Started guide and the "Hide other widgets" option.

= 1.4.1 =
Translation template refreshed. No functional changes.

= 1.4.0 =
New dropdown, Elementor and Bricks template support.

= 1.3.0 =
New top-level menu and more page builders.

= 1.0.0 =
Initial release.
