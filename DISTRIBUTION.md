# Distribution Guide for Bulk Payment WooCommerce

This guide covers all the ways users can download and install your plugin.

## Available Distribution Channels

### 1. GitHub Releases (Primary Method)

**Status:** ✅ Active
**URL:** https://github.com/mmhfarooque/bulk-payment-woocommerce/releases

Users can download official release ZIP files directly from GitHub.

#### For Users:
1. Visit https://github.com/mmhfarooque/bulk-payment-woocommerce/releases
2. Download the latest `bulk-payment-wc-X.X.X.zip` file
3. Go to WordPress Admin > Plugins > Add New > Upload Plugin
4. Upload the ZIP file and activate

#### For You (Creating Releases):
```bash
# Method 1: Using the automated script
./scripts/create-release.sh 1.0.5 "Release notes here"

# Method 2: Manual process
# 1. Create ZIP manually
# 2. Create and push tag
git tag -a v1.0.5 -m "Version 1.0.5"
git push origin v1.0.5

# 3. Create release
gh release create v1.0.5 releases/bulk-payment-wc-1.0.5.zip \
  --title "Version 1.0.5" \
  --notes "Your release notes"
```

---

### 2. GitHub Repository (Direct Download)

**Status:** ✅ Active
**URL:** https://github.com/mmhfarooque/bulk-payment-woocommerce

Users can download the entire repository as a ZIP file.

#### For Users:
1. Visit https://github.com/mmhfarooque/bulk-payment-woocommerce
2. Click the green "Code" button
3. Click "Download ZIP"
4. Upload to WordPress via Plugins > Add New > Upload Plugin

**Note:** This includes development files, so releases are preferred.

---

### 3. Composer/Packagist (For Developers)

**Status:** ⚠️ Ready (Needs Publishing)
**Package:** `jezweb/bulk-payment-wc`

Developers using Composer can install your plugin via command line.

#### Publishing to Packagist:
1. Go to https://packagist.org/
2. Sign in with GitHub
3. Click "Submit" in the top menu
4. Enter: `https://github.com/mmhfarooque/bulk-payment-woocommerce`
5. Click "Check"
6. Follow the instructions to complete submission

#### For Users (After Publishing):
```bash
composer require jezweb/bulk-payment-wc
```

Or add to `composer.json`:
```json
{
  "require": {
    "jezweb/bulk-payment-wc": "^1.0"
  }
}
```

#### Auto-Update on Packagist:
After initial submission, Packagist can auto-update when you:
1. Create new GitHub releases
2. Set up the GitHub service hook (Packagist will guide you)

---

### 4. WordPress Auto-Updates from GitHub

**Status:** ✅ Active (Built into Plugin)

Your plugin includes automatic update checking from GitHub!

#### How It Works:
- Plugin checks GitHub every 12 hours for new releases
- WordPress admin shows update notification when available
- Users can update directly from WordPress dashboard
- No WordPress.org submission needed

#### Features:
- Automatic version checking
- One-click updates from WordPress admin
- "Check for Updates" link in plugin list
- Uses GitHub Releases API
- Cached for performance (12 hours)

#### User Experience:
1. User installs your plugin from any source
2. Plugin automatically checks GitHub for updates
3. Update notification appears in WordPress admin
4. User clicks "Update Now"
5. Plugin updates automatically

---

### 5. Git Clone (For Developers)

**Status:** ✅ Active

Developers can clone directly into their WordPress installation.

#### For Users:
```bash
cd wp-content/plugins/
git clone https://github.com/mmhfarooque/bulk-payment-woocommerce.git bulk-payment-wc
```

To update:
```bash
cd wp-content/plugins/bulk-payment-wc
git pull origin main
```

---

## Comparison Table

| Method | Best For | Auto-Updates | Ease of Use |
|--------|----------|--------------|-------------|
| GitHub Releases | All users | ✅ Yes (built-in) | ⭐⭐⭐⭐⭐ |
| GitHub Repo ZIP | Quick testing | ✅ Yes (built-in) | ⭐⭐⭐⭐ |
| Composer | Developers | ⚠️ Manual | ⭐⭐⭐ |
| Git Clone | Developers | ⚠️ Manual | ⭐⭐ |

---

## Recommended Workflow for Users

### Best Method (Recommended):
**Download from GitHub Releases** → Automatic updates included!

1. Visit https://github.com/mmhfarooque/bulk-payment-woocommerce/releases
2. Download latest ZIP
3. Install via WordPress admin
4. Receive automatic update notifications

### For Developers:
**Use Composer** (after Packagist publishing)

```bash
composer require jezweb/bulk-payment-wc
```

---

## Publishing Checklist

### ✅ Completed:
- [x] GitHub repository created
- [x] GitHub releases setup
- [x] Composer.json created
- [x] Auto-updater implemented
- [x] Release v1.0.4 published
- [x] Release script created

### ⚠️ Optional (Not Required):
- [ ] Publish to Packagist (for Composer users)
- [ ] Submit to WordPress.org (for maximum reach)
- [ ] Create WordPress.org assets (banners, icons)

---

## How Users Get Updates

### Automatic (GitHub Releases):
1. You create a new release on GitHub
2. Users with plugin installed see update notification
3. Users click "Update Now"
4. Plugin updates automatically

### Manual (Composer):
```bash
composer update jezweb/bulk-payment-wc
```

### Manual (Git Clone):
```bash
git pull origin main
```

---

## Creating New Releases

### Quick Method:
```bash
# Bump version (updates files automatically)
./scripts/bump-version.sh patch  # or minor, major

# Create release (creates ZIP, tag, and GitHub release)
./scripts/create-release.sh 1.0.5 "Bug fixes and improvements"
```

### What Happens:
1. ✅ Version bumped in plugin files
2. ✅ Git tag created
3. ✅ ZIP file created
4. ✅ GitHub release published
5. ✅ Users notified automatically

---

## Support Links

Share these with your users:

- **Download:** https://github.com/mmhfarooque/bulk-payment-woocommerce/releases
- **Documentation:** https://github.com/mmhfarooque/bulk-payment-woocommerce#readme
- **Issues:** https://github.com/mmhfarooque/bulk-payment-woocommerce/issues
- **Source Code:** https://github.com/mmhfarooque/bulk-payment-woocommerce

---

## Next Steps (Optional)

### To Reach More Users:

1. **Publish to Packagist** (5 minutes)
   - Visit https://packagist.org/
   - Submit your repository
   - Developers can install via Composer

2. **Submit to WordPress.org** (1-2 weeks)
   - Much wider audience
   - Built-in WordPress installation
   - Requires review process
   - See WordPress.org plugin guidelines

3. **Create Documentation Site**
   - GitHub Pages
   - ReadTheDocs
   - Custom documentation site

---

## Files Reference

- `scripts/create-release.sh` - Automated release creation
- `scripts/bump-version.sh` - Version bumping
- `composer.json` - Composer/Packagist config
- `includes/class-bulk-payment-updater.php` - Auto-update functionality
- `releases/` - Release ZIP files (git-ignored)

---

**You're all set!** Your plugin is now available for download with automatic updates. 🎉
