# Welcomify — Custom Welcome Dashboard

Transform the boring WordPress welcome dashboard into a beautiful design experience.
Compatible with any page builder.

> This is the development repository. The user-facing plugin description lives in
> [`readme.txt`](readme.txt), which is what WordPress.org reads.

- **WordPress.org:** https://wordpress.org/plugins/welcomify/
- **Author:** [Umair Khan](https://umairyousafzai.com/)
- **License:** GPL-2.0-or-later

## Development

The plugin lives directly in a local WordPress install via a symlink, so edits are
live immediately — no copying.

```powershell
# Run once, in an ELEVATED PowerShell
New-Item -ItemType SymbolicLink `
  -Path   "C:\Users\Yousafzai\Local Sites\welcomify\app\public\wp-content\plugins\welcomify" `
  -Target "D:\Projects\Welcomify\welcomify"
```

## Releasing a new version

Deployment to WordPress.org is fully automated by GitHub Actions. Never touch SVN by hand.

1. Bump the version in **three** places — they must match exactly:
   - `welcomify.php` → `Version:` header
   - `welcomify.php` → `UMY_WDW_VER` constant
   - `readme.txt` → `Stable tag:`
2. Add the changelog entry to `readme.txt`.
3. Commit, tag, push:

```powershell
git commit -am "Release 1.5.0"
git tag 1.5.0
git push origin main --tags
```

The [deploy workflow](.github/workflows/deploy.yml) then pushes to SVN `trunk`,
creates `tags/1.5.0`, and syncs the assets.

### Assets-only updates

Changing the banner, icon, screenshots, or the readme description does **not**
need a version bump. Just push to `main` — the
[assets workflow](.github/workflows/assets.yml) handles it.

## Repository layout

| Path | Purpose |
| --- | --- |
| `welcomify.php` | Plugin bootstrap, constants, embed mode |
| `classes/` | Admin class — menus, settings, builder detection |
| `includes/` | Admin screen templates |
| `assets/` | CSS, JS and images that **ship** with the plugin |
| `languages/` | Translation template (`.pot`) |
| `.wordpress-org/` | Banner, icon and screenshots for the WP.org listing (**not** shipped) |
| `.distignore` | Files excluded from the deployed plugin |

## Required GitHub secrets

| Secret | Value |
| --- | --- |
| `SVN_USERNAME` | WordPress.org username |
| `SVN_PASSWORD` | WordPress.org password |
