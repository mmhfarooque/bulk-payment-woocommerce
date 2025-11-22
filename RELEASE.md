# Release Guide for Bulk Payment for WooCommerce

This guide explains how to create and manage releases for this plugin.

## Quick Release Checklist

- [ ] Update version numbers in code
- [ ] Update CHANGELOG.md
- [ ] Test plugin thoroughly
- [ ] Commit changes
- [ ] Create git tag
- [ ] Push to GitHub
- [ ] Create GitHub release
- [ ] Upload plugin ZIP

## Detailed Release Process

### 1. Prepare for Release

Update the version in these locations:
- `bulk-payment-wc.php` (Line 6): Plugin header version
- `bulk-payment-wc.php` (Line 26): `BULK_PAYMENT_WC_VERSION` constant
- `README.md`: If version is mentioned

### 2. Update CHANGELOG.md

Add a new version section:

```markdown
## [X.Y.Z] - YYYY-MM-DD

### Added
- New feature 1
- New feature 2

### Changed
- Updated feature 1

### Fixed
- Bug fix 1
- Bug fix 2
```

### 3. Commit Version Bump

```bash
git add .
git commit -m "Bump version to X.Y.Z"
```

### 4. Create Git Tag

```bash
git tag -a vX.Y.Z -m "Version X.Y.Z - Brief description"
git push origin main --tags
```

### 5. Create Plugin ZIP

Create a distributable package:

```bash
cd "/home/mmhf/Jezweb/Plugin-DEV"
zip -r "bulk-payment-wc-X.Y.Z.zip" "Bulk Payment WC" \
  -x "*/.git/*" \
  -x "*/node_modules/*" \
  -x "*/tests/*" \
  -x "*/.DS_Store" \
  -x "*/.gitignore" \
  -x "*/CONTRIBUTING.md" \
  -x "*/RELEASE.md"
```

### 6. Create GitHub Release

1. Go to https://github.com/YOUR_USERNAME/bulk-payment-for-woocommerce/releases
2. Click "Create a new release"
3. Select tag: vX.Y.Z
4. Release title: "Version X.Y.Z"
5. Description: Copy from CHANGELOG.md
6. Attach the ZIP file
7. Click "Publish release"

## Version Numbering (Semantic Versioning)

- **MAJOR (X.0.0)**: Breaking changes, incompatible API changes
- **MINOR (1.X.0)**: New features, backwards compatible
- **PATCH (1.0.X)**: Bug fixes, backwards compatible

### Examples:
- `1.0.5` → Bug fix release
- `1.1.0` → New feature added
- `2.0.0` → Major update with breaking changes

## Automated Release Commands

### Quick Version Bump (Patch)

```bash
# For bug fixes (1.0.4 → 1.0.5)
./scripts/bump-version.sh patch
```

### Minor Version Bump

```bash
# For new features (1.0.5 → 1.1.0)
./scripts/bump-version.sh minor
```

### Major Version Bump

```bash
# For breaking changes (1.1.0 → 2.0.0)
./scripts/bump-version.sh major
```

## Distribution

### Users Can Download Via:

1. **GitHub Releases** (Recommended)
   - Navigate to: https://github.com/YOUR_USERNAME/bulk-payment-for-woocommerce/releases
   - Download latest ZIP
   - Install via WordPress admin

2. **Git Clone** (For Developers)
   ```bash
   cd wp-content/plugins
   git clone https://github.com/YOUR_USERNAME/bulk-payment-for-woocommerce.git
   ```

3. **WordPress Plugin Directory** (Future)
   - Submit to wordpress.org/plugins after establishing the plugin

## Post-Release

1. Announce on social media
2. Update documentation site (if any)
3. Notify users of important changes
4. Monitor for issues

## Emergency Hotfix Process

If a critical bug is found after release:

1. Create hotfix branch: `git checkout -b hotfix-X.Y.Z`
2. Fix the issue
3. Update version (increment patch: X.Y.Z+1)
4. Update CHANGELOG.md
5. Commit and tag
6. Merge to main
7. Create emergency release

## Notes

- Always test on a staging site before releasing
- Keep CHANGELOG.md updated with every change
- Tag versions consistently (vX.Y.Z format)
- Include clear upgrade instructions for major versions
