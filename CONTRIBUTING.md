# Contributing to Bulk Payment for WooCommerce

Thank you for your interest in contributing to Bulk Payment for WooCommerce!

## Development Setup

### Prerequisites
- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce 6.0 or higher
- Git

### Local Development
1. Clone the repository
2. Install WordPress and WooCommerce locally
3. Symlink or copy the plugin to your WordPress plugins directory
4. Activate the plugin

## Version Management

This plugin follows [Semantic Versioning](https://semver.org/):
- MAJOR version for incompatible API changes
- MINOR version for new functionality in a backwards compatible manner
- PATCH version for backwards compatible bug fixes

### Releasing a New Version

1. **Update Version Numbers**
   - Update version in `bulk-payment-wc.php` header (Line 6)
   - Update `BULK_PAYMENT_WC_VERSION` constant (Line 26)
   - Update the README.md if needed

2. **Update CHANGELOG.md**
   - Add new version section at the top
   - List all changes under appropriate categories (Added, Changed, Fixed, etc.)
   - Update the date

3. **Commit Changes**
   ```bash
   git add .
   git commit -m "Release version X.Y.Z"
   ```

4. **Create Git Tag**
   ```bash
   git tag -a vX.Y.Z -m "Version X.Y.Z"
   git push origin main --tags
   ```

5. **Create GitHub Release**
   - Go to GitHub Releases
   - Create a new release from the tag
   - Copy changelog content
   - Upload plugin ZIP file

## Creating a Release Package

To create a distributable ZIP file:

```bash
# From the plugin directory
zip -r bulk-payment-wc-X.Y.Z.zip . \
  -x "*.git*" \
  -x "node_modules/*" \
  -x "tests/*" \
  -x "*.md" \
  -x ".DS_Store"
```

Or include documentation:
```bash
zip -r bulk-payment-wc-X.Y.Z.zip . \
  -x "*.git*" \
  -x "node_modules/*" \
  -x "tests/*" \
  -x ".DS_Store"
```

## Code Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Use meaningful variable and function names
- Comment complex logic
- Test with WooCommerce compatibility

## Testing

Before releasing:
- Test on supported WordPress versions
- Test on supported WooCommerce versions
- Test on supported PHP versions
- Check for JavaScript errors
- Verify admin panel functionality
- Test frontend shortcode and Elementor widget

## Pull Requests

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request with a clear description

## Questions?

Open an issue or contact the maintainer.
