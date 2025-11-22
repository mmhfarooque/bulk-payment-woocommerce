# Bulk Payment for WooCommerce

Allow customers to pay any amount of money for symbolic products without shipping information. Perfect for donations, custom payments, and flexible pricing.

## Description

Bulk Payment for WooCommerce is a powerful plugin that transforms your WooCommerce products into flexible payment solutions. Customers can enter any amount they wish to pay (within optional min/max limits), and the checkout process is streamlined to skip unnecessary shipping information.

### Perfect For:
- Donation campaigns
- Custom payment amounts
- Flexible pricing products
- Service payments
- Gift contributions
- Symbolic products
- Pay-what-you-want products

## Features

- **Custom Amount Input**: Let customers enter their own payment amount
- **Configurable Limits**: Set minimum and maximum payment amounts per product
- **Skip Shipping**: Automatically removes shipping fields for bulk payment products
- **Flexible Configuration**: Enable bulk payment on any product individually
- **Global Settings**: Set default values for all bulk payment products
- **Translation Ready**: Fully translatable with .pot file included
- **Admin Interface**: Easy-to-use settings page and product metabox
- **Quick Edit Support**: Enable/disable bulk payment from product list
- **Custom Labels**: Customize field labels and placeholder text
- **Works with All Payment Gateways**: Compatible with any WooCommerce payment gateway

## Installation

1. Upload the plugin files to `/wp-content/plugins/bulk-payment-wc/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce > Bulk Payment to configure global settings
4. Edit any product and enable "Bulk Payment" in the sidebar metabox

## Configuration

### Global Settings

Navigate to **WooCommerce > Bulk Payment** to configure:

- Enable bulk payment for all products by default
- Set default minimum amount
- Set default maximum amount
- Customize default labels and placeholders
- Hide regular price for bulk payment products

### Per-Product Settings

When editing a product, find the **Bulk Payment Options** metabox in the sidebar:

1. **Enable Bulk Payment**: Check to enable for this product
2. **Amount Field Label**: Customize the label (e.g., "Donation Amount")
3. **Placeholder Text**: Text shown in the input field
4. **Minimum Amount**: Lowest acceptable payment amount
5. **Maximum Amount**: Highest acceptable payment amount (optional)

## Usage

### For Administrators:

1. Edit any WooCommerce product
2. In the sidebar, find "Bulk Payment Options"
3. Check "Enable Bulk Payment"
4. Configure minimum/maximum amounts (optional)
5. Customize labels and placeholder text
6. Save the product

### For Customers:

1. Visit a bulk payment enabled product
2. Enter desired amount in the custom field
3. Add to cart
4. Proceed to checkout (no shipping required)
5. Complete payment

## Screenshots

1. Product page with custom amount input
2. Admin product metabox settings
3. Global settings page
4. Checkout without shipping fields
5. Product list with bulk payment column

## Frequently Asked Questions

### Can I set different amounts for different products?
Yes! Each product has its own bulk payment settings including minimum and maximum amounts.

### Will shipping be required for bulk payment products?
No. Bulk payment products automatically skip shipping information during checkout.

### Can I mix regular and bulk payment products in one order?
Yes. If the cart contains both types, shipping will only be required for regular products.

### Does this work with all payment gateways?
Yes. The plugin works with any WooCommerce payment gateway.

### Can I translate the plugin?
Yes. The plugin is translation ready. Use the included .pot file in the /languages directory.

### What happens if a customer enters an amount outside the min/max range?
The plugin validates the amount and displays an error message, preventing them from adding to cart.

## Technical Details

### Requirements

- WordPress 5.8 or higher
- WooCommerce 6.0 or higher
- PHP 7.4 or higher

### File Structure

```
bulk-payment-wc/
├── bulk-payment-wc.php          # Main plugin file
├── includes/
│   ├── class-bulk-payment-product.php   # Product handling
│   ├── class-bulk-payment-cart.php      # Cart functionality
│   ├── class-bulk-payment-checkout.php  # Checkout customization
│   └── class-bulk-payment-admin.php     # Admin interface
├── assets/
│   └── css/
│       └── admin.css             # Admin styles
├── languages/
│   └── bulk-payment-wc.pot      # Translation template
└── README.md                     # This file
```

### Hooks and Filters

#### Actions

- `bulk_payment_before_amount_field` - Before the amount input field
- `bulk_payment_after_amount_field` - After the amount input field
- `bulk_payment_product_saved` - After product meta is saved

#### Filters

- `bulk_payment_checkout_fields` - Modify checkout fields for bulk payment orders
- `bulk_payment_min_amount` - Filter minimum amount
- `bulk_payment_max_amount` - Filter maximum amount
- `bulk_payment_custom_price_html` - Filter price display

### Code Examples

#### Check if a product has bulk payment enabled:

```php
$product_id = 123;
$enabled = get_post_meta($product_id, '_bulk_payment_enabled', true);
if ($enabled === 'yes') {
    // Product has bulk payment enabled
}
```

#### Get bulk payment amount from order item:

```php
foreach ($order->get_items() as $item) {
    $amount = $item->get_meta('_bulk_payment_amount');
    if ($amount) {
        echo 'Bulk payment amount: ' . wc_price($amount);
    }
}
```

## Changelog

### 1.0.4 - 2025-11-21
**Major Improvements & Critical Fixes:**
- **CRITICAL FIX:** Completely removed address fields from checkout (not just hidden)
  - Changed from making fields optional to completely unsetting them via unset()
  - Added aggressive CSS hiding with multiple selectors
  - Ensures checkout never asks for shipping/billing address for bulk payment products
- **Fixed:** Button hover text visibility issue
  - Changed hover background to darker primary shade instead of accent color
  - Ensures white text remains visible on hover state
- **Style Customization System:**
  - Reverted default CSS to neutral colors (gray/blue theme)
  - Added comprehensive admin settings for color customization:
    - Primary color (buttons, borders, headings)
    - Secondary color (backgrounds)
    - Text color (all text elements)
    - Accent color (hover states, focus effects)
    - Font family selection (8 options including web-safe fonts)
  - Added WordPress color picker integration
  - Dynamic CSS injection based on admin settings
  - Empty settings use default neutral theme
  - Color changes apply instantly without code modification
- Enhanced checkout field removal for bulk payment products
- Improved user experience with customizable branding

### 1.0.3 - 2025-11-21
**Design Update:**
- Applied brand color scheme throughout plugin
  - Primary: #E24E5B (coral red)
  - Secondary: #FFF3F2 (soft pink - common background)
  - Text: #4C4646 (warm gray)
  - Accent: #C8E3EF (light blue)
- Added Baskerville Poster PT font family (normal and bold)
- Updated all frontend form colors and styling
- Updated all admin interface colors
- Enhanced focus states with accent color
- Improved visual consistency across all interfaces

### 1.0.2 - 2025-11-21
**Critical Fix:**
- Fixed fatal error when creating pages in admin: Added WC()->session null check in prefill_checkout_fields()
- Plugin now properly checks if WooCommerce session is available before accessing it
- Prevents "Call to a member function get() on null" error in admin context

### 1.0.1 - 2025-11-21
**Bug Fixes:**
- Fixed critical error: Removed non-existent Elementor widget file requirement
- Fixed image upload functionality in admin settings (added wp_enqueue_media)
- Enhanced responsive CSS with comprehensive breakpoints for all devices
- Added mobile-optimized styles for customer input fields
- Improved tablet landscape and portrait layouts
- Added support for extra small devices (360px and below)

**Improvements:**
- Better touch targets for mobile devices (minimum 44px)
- Optimized font sizes across all screen sizes
- Improved spacing and padding on mobile devices
- Enhanced print styles for customer fields

### 1.0.0 - 2025-11-21
- Initial release
- Custom amount input on product pages
- Configurable min/max amounts per product
- Automatic shipping skip for bulk payment products
- Admin settings page
- Product metabox for bulk payment options
- Quick edit support
- Translation ready
- Auto-created bulk payment product
- Shortcode support: [bulk_payment_form]
- Elementor widget integration
- Two checkout types: Direct (Pay Now) and Regular (Cart)
- Customer info collection (Name, Email, Phone)
- AJAX-powered form submission
- Fully responsive design

## Support

For support, please visit:
- Plugin URI: https://jezweb.com
- Author: Mahmud Farooque
- Author URI: https://jezweb.com

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
```

## Credits

Developed by Mahmud Farooque for Jezweb
Website: https://jezweb.com

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Roadmap

Future features under consideration:
- Multiple currency support
- Suggested amount buttons (e.g., $10, $25, $50)
- Progress bar for donation goals
- Thank you message customization
- Email notifications for specific amounts
- Export bulk payment reports
- Integration with popular form builders

---

Made with ❤️ by [Jezweb](https://jezweb.com)
