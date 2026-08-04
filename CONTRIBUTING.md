# Development & Release

Internal notes for working on this plugin. None of this ships to WordPress.org.

## Local setup

Point a local WordPress install at this repository with a directory link, so edits
are live immediately and nothing has to be copied:

```powershell
# Windows — run once. Adjust both paths to your machine.
New-Item -ItemType Junction `
  -Path   "<local-site>\app\public\wp-content\plugins\welcomify" `
  -Target "<path-to-this-repo>"
```

```bash
# macOS / Linux
ln -s /path/to/this/repo /path/to/wp-content/plugins/welcomify
```

## Releasing

Deployment is automated by GitHub Actions. **Never touch SVN by hand.**

1. Bump the version in **three** places — they must match exactly:
   - `welcomify.php` → `Version:` header
   - `welcomify.php` → `UMY_WDW_VER` constant
   - `readme.txt` → `Stable tag:`
2. Add the changelog entry to `readme.txt`.
3. Regenerate the translation template if strings changed (see below).
4. Commit, tag, push:

```bash
git commit -am "Release 1.5.0"
git tag 1.5.0
git push origin main --tags
```

The [deploy workflow](.github/workflows/deploy.yml) pushes to SVN `trunk`, creates
`tags/1.5.0`, and syncs the listing assets.

### Assets-only updates

Changing the banner, icon, screenshots, or the readme description does **not** need a
version bump. Push to `main` and the [assets workflow](.github/workflows/assets.yml)
handles it.

## Regenerating translations

Run whenever translatable strings are added or changed:

```bash
wp i18n make-pot . languages/welcomify.pot \
  --slug=welcomify \
  --domain=welcomify \
  --package-name="Welcomify" \
  --exclude=.wordpress-org,.github,node_modules \
  --headers='{"Report-Msgid-Bugs-To":"https://wordpress.org/support/plugin/welcomify/"}'
```

## Repository layout

| Path | Purpose | Ships? |
| --- | --- | --- |
| `welcomify.php` | Plugin bootstrap, constants, embed mode | ✅ |
| `classes/` | Admin class — menus, settings, builder detection | ✅ |
| `includes/` | Admin screen templates | ✅ |
| `assets/` | CSS, JS and images used by the plugin | ✅ |
| `languages/` | Translation template (`.pot`) | ✅ |
| `.wordpress-org/` | Banner, icon and screenshots for the WP.org listing | ❌ |
| `.distignore` | Files excluded from the deployed plugin | ❌ |

## Conventions

All internal identifiers use the `umy_wdw_` / `UMY_WDW_` / `umy-wdw-` prefix. Style
and script handles, option keys, and HTML IDs depend on it across PHP, CSS and JS —
do not rename it piecemeal.

Text domain is `welcomify` everywhere.

## Required GitHub secrets

| Secret | Value |
| --- | --- |
| `SVN_USERNAME` | WordPress.org username |
| `SVN_PASSWORD` | WordPress.org account password |
