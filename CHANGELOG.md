# Changelog

All notable changes to Bulk Payment for WooCommerce will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.9] - 2025-11-26

### Fixed
- **Product Purchasable Fix** - Fixed "Sorry, this product cannot be purchased" error
  - Added `woocommerce_is_purchasable` filter to make bulk payment products always purchasable
  - WooCommerce by default considers $0 products as not purchasable - this is now overridden for bulk payment products

### Technical
- Added `make_bulk_payment_purchasable()` method to `Bulk_Payment_Cart` class

## [1.0.8] - 2025-11-26

### Fixed
- **Guest Checkout Support (Improved)** - Enhanced session handling for non-logged-in users
  - Improved WooCommerce session initialization with proper cookie handling
  - Added `set_customer_session_cookie(true)` to establish session for guests
  - Better error messages now show actual WooCommerce error instead of generic "Failed to add to cart"
- **WordPress 6.7+ Compatibility** - Fixed translation loading too early warning
  - Moved `load_plugin_textdomain()` to `init` action as required by WordPress 6.7+
- **PHP 8.1+ Compatibility** - Fixed float to int conversion deprecation warnings
  - Cast color values to int in `darken_color()` function before passing to `dechex()`

### Technical
- Improved `ensure_wc_session()` to check `has_session()` and set session cookie
- Added `wc_clear_notices()` before add-to-cart to capture actual error messages
- Added error notice extraction to show real WooCommerce errors to users
- Moved textdomain loading to separate `load_textdomain()` method hooked to `init`

## [1.0.7] - 2025-11-26

### Fixed
- **Guest Checkout Support** - Fixed "Failed to add to cart" error for non-logged-in users
  - Plugin now forces guest checkout to be enabled for bulk payment products
  - Added WooCommerce session initialization for guest users during AJAX requests
  - Registration is no longer required when cart contains bulk payment products

### Technical
- Added `force_guest_checkout()` filter on `pre_option_woocommerce_enable_guest_checkout`
- Added `disable_registration_required()` filter on `woocommerce_checkout_registration_required`
- Added `ensure_wc_session()` helper method to initialize WC session, customer, and cart for guests
- Updated both `ajax_add_to_cart()` and `ajax_direct_checkout()` handlers with session initialization

## [1.0.6] - 2025-11-22

### Purpose
- **Auto-Update Test Release** - This version tests the GitHub-based automatic update system
- Verifies that users on v1.0.5 can detect and install updates automatically
- No functional changes from v1.0.5

### Technical
- Added version test comment to verify update detection

## [1.0.5] - 2025-11-22

### Changed
- Updated Pay Now button font to "baskerville-poster-pt" for improved typography
- Increased Pay Now button border-radius from 6px to 7px for smoother corners

### Added
- GitHub-based automatic update system
- Composer package support (jezweb/bulk-payment-wc)
- Automated release creation script

## [1.0.4] - 2025-11-21

### Features
- Allow customers to pay any amount for a symbolic product
- Skip shipping information collection
- Elementor widget integration
- Shortcode support for easy embedding
- Custom product type for bulk payments
- Flexible pricing with minimum amount validation
- Admin settings panel
- WooCommerce compatibility

### Requirements
- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce 6.0 or higher

---

## Version History

### [1.0.9] - Current Release
Fixed "product cannot be purchased" error for bulk payment products.

### [1.0.8] - Previous Release
Improved guest checkout, WordPress 6.7+ and PHP 8.1+ compatibility fixes.

### [1.0.7] - Previous Release
Guest checkout fix - non-logged-in users can now add to cart and checkout.

### [1.0.6] - Previous Release
Auto-update test release - verifies update system works correctly.

### [1.0.5] - Previous Release
Version with auto-updates and improved button styling.

### [1.0.4] - Previous Release
Initial public release with all core features.

---

## How to Update

### For Users
1. Download the latest release from the [Releases](https://github.com/YOUR_USERNAME/bulk-payment-for-woocommerce/releases) page
2. Deactivate the current plugin in WordPress
3. Delete the old plugin files
4. Upload and activate the new version

### For Developers
See [CONTRIBUTING.md](CONTRIBUTING.md) for development and release guidelines.

---

## Types of Changes
- **Added** for new features
- **Changed** for changes in existing functionality
- **Deprecated** for soon-to-be removed features
- **Removed** for now removed features
- **Fixed** for any bug fixes
- **Security** for vulnerability fixes
