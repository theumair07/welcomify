# Welcomify — Custom Welcome Dashboard

Transform the boring WordPress welcome dashboard into a beautiful design experience.
Compatible with any page builder.

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/welcomify)](https://wordpress.org/plugins/welcomify/)
[![License](https://img.shields.io/badge/license-GPLv2%2B-blue)](license.txt)

**Welcomify** lets you replace the default WordPress welcome panel with any page you
create using your favorite page builder — Elementor, Gutenberg, Bricks, Divi, and more.

## Features

- **Role-Based Dashboards** — assign a different welcome page to each user role
  (Administrator, Editor, Author, Contributor, Subscriber)
- **Page Builder Compatible** — Elementor, Gutenberg, Bricks, Divi, Beaver Builder,
  Breakdance, and more
- **Dismissible Option** — let users dismiss the welcome panel, per role
- **Clean Embed** — pages display without headers, footers, or sidebars
- **Multisite Support** — full support for WordPress multisite networks
- **Lightweight** — no bloat, just the features you need
- **Translation Ready** — fully internationalized

## Requirements

| | |
| --- | --- |
| WordPress | 5.2 or higher |
| PHP | 7.4 or higher |

## Installation

Install from the WordPress plugin directory:

**Plugins → Add New → search "Welcomify" → Install → Activate**

Or manually: upload the plugin folder to `/wp-content/plugins/` and activate it from
the **Plugins** menu.

## How it works

Welcomify offers two ways to pick your dashboard content, depending on your builder.

### Elementor & Bricks (templates)

1. Go to **Elementor → Templates** or **Bricks → Templates** and create a template
2. Design your welcome content and save
3. Go to **Welcomify** in the admin menu
4. Pick your template from the **Elementor Templates** or **Bricks Templates** group
5. Assign it to a user role and save

### Gutenberg, Divi, Beaver Builder, Breakdance & others (pages)

1. Create a WordPress page with your preferred builder
2. Design your welcome content — the page may be published, private, draft, or
   password protected
3. Go to **Welcomify** in the admin menu
4. Pick your page from the **Pages** group
5. Assign it to a user role and save

Users now see your custom content instead of the default WordPress welcome panel.

## Use cases

- Branded onboarding for new users
- Announcements targeted at specific roles
- Quick-start guides for content creators
- Analytics dashboards for administrators
- Custom welcome messages for clients

## FAQ

**Does this require Elementor?**
No. Elementor is fully supported, but any page builder — or plain Gutenberg — works.

**Will this work with my theme?**
Yes. The page is embedded in an iframe, so it renders independently of your theme.

**What happens if I delete an assigned page?**
Welcomify detects it and falls back to the default WordPress welcome panel.

**Can I use draft, private, or password protected pages?**
Yes — useful for welcome pages you don't want visible on the front end.

## Links

- [Plugin page on WordPress.org](https://wordpress.org/plugins/welcomify/)
- [Support forum](https://wordpress.org/support/plugin/welcomify/)
- [Changelog](readme.txt)

## Contributing

Development setup and the release process are documented in
[CONTRIBUTING.md](CONTRIBUTING.md).

## License

GPL-2.0-or-later — see [license.txt](license.txt).

Built by [Umair Khan](https://umairyousafzai.com/).
