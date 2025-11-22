# Update Workflow for Bulk Payment WooCommerce

This guide explains how to push updates from your local development to GitHub so users can download the latest version.

## GitHub Repository

**Repository URL:** https://github.com/mmhfarooque/bulk-payment-woocommerce

## Quick Update Workflow

When you make changes to the plugin locally, follow these steps to update GitHub:

### 1. Make Your Changes
Edit your plugin files as needed (code, documentation, etc.)

### 2. Update Version Number
When releasing a new version, update the version in these files:
- `bulk-payment-wc.php` (line 6 and line 26)
- `CHANGELOG.md`
- `README.md` (if needed)

### 3. Stage Your Changes
```bash
git add .
```

### 4. Commit Your Changes
```bash
git commit -m "Description of your changes"
```

Example commit messages:
- `git commit -m "Version 1.0.5: Fix checkout validation bug"`
- `git commit -m "Add new feature: suggested donation amounts"`
- `git commit -m "Update documentation"`

### 5. Push to GitHub
```bash
git push origin main
```

That's it! Your changes are now on GitHub for users to download.

## Alternative: Single Command
You can combine steps 3-5 into one command:
```bash
git add . && git commit -m "Your commit message" && git push origin main
```

## Creating a Release (Recommended for Version Updates)

For major version updates, create a tagged release:

### 1. Create and push a version tag
```bash
git tag -a v1.0.5 -m "Version 1.0.5"
git push origin v1.0.5
```

### 2. Create a release on GitHub
```bash
gh release create v1.0.5 --title "Version 1.0.5" --notes "Release notes here"
```

Or manually:
1. Go to https://github.com/mmhfarooque/bulk-payment-woocommerce/releases
2. Click "Draft a new release"
3. Choose your tag (e.g., v1.0.5)
4. Add release title and notes
5. Click "Publish release"

## Download Instructions for Users

Users can download your plugin in these ways:

### Method 1: Download ZIP from GitHub
1. Visit https://github.com/mmhfarooque/bulk-payment-woocommerce
2. Click the green "Code" button
3. Click "Download ZIP"
4. Upload to WordPress via Plugins > Add New > Upload Plugin

### Method 2: Clone with Git
```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/mmhfarooque/bulk-payment-woocommerce.git
```

### Method 3: Download Release (if you create releases)
1. Visit https://github.com/mmhfarooque/bulk-payment-woocommerce/releases
2. Download the latest release ZIP
3. Upload to WordPress

## Checking Current Status

Before making changes, always check your git status:
```bash
git status
```

To see what changed:
```bash
git diff
```

To see commit history:
```bash
git log --oneline
```

## Automatic Updates for WordPress (Advanced)

If you want WordPress to automatically detect updates from GitHub, you'll need to:
1. Use a plugin update service like WP Pusher or GitHub Updater
2. Or implement the WordPress Update API in your plugin

Let me know if you'd like help setting up automatic WordPress updates!

## Quick Reference

| Task | Command |
|------|---------|
| Check status | `git status` |
| Stage all changes | `git add .` |
| Commit changes | `git commit -m "message"` |
| Push to GitHub | `git push origin main` |
| Create tag | `git tag -a v1.0.5 -m "Version 1.0.5"` |
| Push tag | `git push origin v1.0.5` |
| View history | `git log --oneline` |
| View remote URL | `git remote -v` |

## Notes

- Always test your changes locally before pushing
- Use meaningful commit messages that describe what changed
- Update version numbers for each release
- Keep your CHANGELOG.md up to date
- Consider creating releases for major versions

## Need Help?

- Git Documentation: https://git-scm.com/doc
- GitHub CLI Documentation: https://cli.github.com/manual/
- GitHub Guides: https://guides.github.com/
