/**
 * Bulk Payment for WooCommerce - Frontend Scripts
 *
 * @package Bulk_Payment_WC
 */

(function($) {
    'use strict';

    /**
     * Bulk Payment Form Handler
     */
    var BulkPaymentForm = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            $(document).on('submit', '.bulk-payment-form', this.handleSubmit.bind(this));
            $(document).on('input', '.bulk-payment-amount-input', this.validateAmount.bind(this));
        },

        /**
         * Validate amount
         */
        validateAmount: function(e) {
            var $input = $(e.currentTarget);
            var amount = parseFloat($input.val());
            var min = parseFloat($input.data('min'));
            var max = parseFloat($input.data('max'));
            var $form = $input.closest('.bulk-payment-form');
            var $message = $form.find('.bulk-payment-message');

            // Clear previous messages
            $message.hide().removeClass('error success info').html('');

            // Validate amount
            if (isNaN(amount) || amount <= 0) {
                return;
            }

            // Check minimum
            if (min && amount < min) {
                this.showMessage($form, 'error', bulkPaymentData.i18n.minAmount + ' ' + this.formatPrice(min));
                return;
            }

            // Check maximum
            if (max && amount > max) {
                this.showMessage($form, 'error', bulkPaymentData.i18n.maxAmount + ' ' + this.formatPrice(max));
                return;
            }
        },

        /**
         * Handle form submit
         */
        handleSubmit: function(e) {
            e.preventDefault();

            var $form = $(e.currentTarget);
            var $button = $form.find('.bulk-payment-submit-button');
            var $message = $form.find('.bulk-payment-message');
            var $amountInput = $form.find('.bulk-payment-amount-input');
            var amount = parseFloat($amountInput.val());

            // Validate amount
            if (!this.isValidAmount($amountInput)) {
                return false;
            }

            // Check if direct checkout or cart flow
            var checkoutType = $form.data('checkout-type') || 'cart';

            if (checkoutType === 'direct') {
                // Validate customer fields
                if (!this.validateCustomerFields($form)) {
                    return false;
                }
                this.handleDirectCheckout($form, amount);
            } else {
                // Regular cart flow
                this.handleCartCheckout($form, amount);
            }

            return false;
        },

        /**
         * Validate amount
         */
        isValidAmount: function($input) {
            var amount = parseFloat($input.val());
            var min = parseFloat($input.data('min'));
            var max = parseFloat($input.data('max'));
            var $form = $input.closest('.bulk-payment-form');

            // Check if amount is valid
            if (isNaN(amount) || amount <= 0) {
                this.showMessage($form, 'error', bulkPaymentData.i18n.invalidAmount);
                return false;
            }

            // Check minimum
            if (min && amount < min) {
                this.showMessage($form, 'error', bulkPaymentData.i18n.minAmount + ' ' + this.formatPrice(min));
                return false;
            }

            // Check maximum
            if (max && amount > max) {
                this.showMessage($form, 'error', bulkPaymentData.i18n.maxAmount + ' ' + this.formatPrice(max));
                return false;
            }

            return true;
        },

        /**
         * Validate customer fields
         */
        validateCustomerFields: function($form) {
            var isValid = true;
            var $customerFields = $form.find('.bulk-payment-customer-fields');

            if ($customerFields.length === 0) {
                return true;
            }

            $customerFields.find('input[required]').each(function() {
                var $input = $(this);
                var value = $input.val().trim();

                if (value === '') {
                    isValid = false;
                    $input.addClass('error');
                } else {
                    $input.removeClass('error');
                }

                // Email validation
                if ($input.attr('type') === 'email' && value !== '') {
                    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(value)) {
                        isValid = false;
                        $input.addClass('error');
                    }
                }
            });

            if (!isValid) {
                this.showMessage($form, 'error', 'Please fill in all required fields.');
            }

            return isValid;
        },

        /**
         * Handle cart checkout
         */
        handleCartCheckout: function($form, amount) {
            var $button = $form.find('.bulk-payment-submit-button');
            var $message = $form.find('.bulk-payment-message');
            var productId = $form.find('input[name="product_id"]').val();
            var nonce = $form.find('input[name="bulk_payment_nonce"]').val();

            // Disable button and show loading
            $button.prop('disabled', true).addClass('loading');
            $message.hide().removeClass('error success info').html('');

            // AJAX request
            $.ajax({
                url: bulkPaymentData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'bulk_payment_add_to_cart',
                    product_id: productId,
                    amount: amount,
                    bulk_payment_nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        this.showMessage($form, 'success', response.data.message);

                        // Update cart count if available
                        if (response.data.cart_count) {
                            $('.cart-contents-count').text(response.data.cart_count);
                        }

                        // Redirect to cart after 1 second
                        setTimeout(function() {
                            window.location.href = response.data.cart_url;
                        }, 1000);

                    } else {
                        this.showMessage($form, 'error', response.data.message || bulkPaymentData.i18n.error);
                        $button.prop('disabled', false).removeClass('loading');
                    }
                }.bind(this),
                error: function() {
                    this.showMessage($form, 'error', bulkPaymentData.i18n.error);
                    $button.prop('disabled', false).removeClass('loading');
                }.bind(this)
            });
        },

        /**
         * Handle direct checkout
         */
        handleDirectCheckout: function($form, amount) {
            var $button = $form.find('.bulk-payment-submit-button');
            var $message = $form.find('.bulk-payment-message');
            var productId = $form.find('input[name="product_id"]').val();
            var nonce = $form.find('input[name="bulk_payment_nonce"]').val();

            // Get customer data
            var customerData = {
                name: $form.find('input[name="customer_name"]').val(),
                email: $form.find('input[name="customer_email"]').val(),
                phone: $form.find('input[name="customer_phone"]').val()
            };

            // Disable button and show loading
            $button.prop('disabled', true).addClass('loading');
            $message.hide().removeClass('error success info').html('');

            // AJAX request for direct checkout
            $.ajax({
                url: bulkPaymentData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'bulk_payment_direct_checkout',
                    product_id: productId,
                    amount: amount,
                    customer_data: customerData,
                    bulk_payment_nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Redirect to payment
                        window.location.href = response.data.checkout_url;
                    } else {
                        this.showMessage($form, 'error', response.data.message || bulkPaymentData.i18n.error);
                        $button.prop('disabled', false).removeClass('loading');
                    }
                }.bind(this),
                error: function() {
                    this.showMessage($form, 'error', bulkPaymentData.i18n.error);
                    $button.prop('disabled', false).removeClass('loading');
                }.bind(this)
            });
        },

        /**
         * Show message
         */
        showMessage: function($form, type, message) {
            var $message = $form.find('.bulk-payment-message');
            $message
                .removeClass('error success info')
                .addClass(type)
                .html(message)
                .fadeIn();
        },

        /**
         * Format price
         */
        formatPrice: function(amount) {
            return bulkPaymentData.currency + parseFloat(amount).toFixed(2);
        }
    };

    /**
     * Document ready
     */
    $(document).ready(function() {
        BulkPaymentForm.init();
    });

})(jQuery);
