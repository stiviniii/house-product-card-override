/**
 * Quick Buy Drawer Handler
 */
(function ($) {
    'use strict';

    var QuickBuy = {
        requests: {}, // Cache for AJAX requests
        cacheContent: {}, // Cache for HTML content

        init: function () {
            this.cache();
            this.bindEvents();
        },

        cache: function () {
            this.$body = $('body');
            this.$overlay = $('.hpco-drawer-overlay');
            this.$drawer = $('.hpco-drawer');
            this.$drawerBody = this.$drawer.find('.hpco-drawer__body');
            this.$closeBtn = this.$drawer.find('.hpco-drawer__close');
            this.loaderHtml = '<div class="hpco-drawer__loader"><div class="hpco-spinner"></div></div>';
        },

        bindEvents: function () {
            var self = this;

            // Open drawer on click
            $(document).on('click', '.hpco-quick-buy-btn', function (e) {
                e.preventDefault();
                var productId = $(this).data('product-id');
                self.openDrawer(productId);
            });

            // Pre-load on hover
            $(document).on('mouseenter', '.hpco-quick-buy-btn', function () {
                var productId = $(this).data('product-id');
                self.preLoadProduct(productId);
            });

            // Close drawer
            this.$closeBtn.on('click', function () {
                self.closeDrawer();
            });

            this.$overlay.on('click', function () {
                self.closeDrawer();
            });

            // Close on ESC
            $(document).on('keydown', function (e) {
                if (e.keyCode === 27 && self.$drawer.hasClass('active')) {
                    self.closeDrawer();
                }
            });
        },

        preLoadProduct: function (productId) {
            if (this.cacheContent[productId] || this.requests[productId]) {
                return;
            }

            this.requests[productId] = $.ajax({
                url: hpcoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hpco_load_quick_buy',
                    product_id: productId,
                    nonce: hpcoData.nonce
                }
            });
        },

        openDrawer: function (productId) {
            var self = this;

            // Reset header content immediately to prevent "flash" of stale product data
            this.$drawer.find('.hpco-drawer__thumbnail').html('');
            this.$drawer.find('.hpco-drawer__title').text('...');
            this.$drawer.find('.hpco-drawer__price').html('');

            // Show drawer and overlay
            this.$overlay.addClass('active');
            this.$drawer.addClass('active');
            this.$body.addClass('hpco-drawer-open');

            // If we have cached content, use it immediately
            if (this.cacheContent[productId]) {
                this.renderContent(this.cacheContent[productId]);
                return;
            }

            // Set loading state for the body
            this.$drawerBody.html(this.loaderHtml);

            // Fetch content via AJAX or use existing request
            var request = this.requests[productId] || $.ajax({
                url: hpcoData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'hpco_load_quick_buy',
                    product_id: productId,
                    nonce: hpcoData.nonce
                }
            });

            request.done(function (response) {
                if (response.success) {
                    self.cacheContent[productId] = response.data;
                    self.renderContent(response.data);
                } else {
                    self.$drawerBody.html('<div class="hpco-error">' + response.data.message + '</div>');
                    self.$drawer.find('.hpco-drawer__title').text('Error');
                }
            }).fail(function () {
                self.$drawerBody.html('<div class="hpco-error">Something went wrong. Please try again.</div>');
                self.$drawer.find('.hpco-drawer__title').text('Error');
            }).always(function () {
                delete self.requests[productId];
            });
        },

        renderContent: function (data) {
            var self = this;
            this.$drawerBody.html(data.html);
            this.$drawer.find('.hpco-drawer__thumbnail').html(data.image);
            this.$drawer.find('.hpco-drawer__title').text(data.title);
            this.$drawer.find('.hpco-drawer__price').html(data.price);

            // Trigger necessary initializations
            $(document.body).trigger('updated_wc_div');
            $(document.body).trigger('post-load');

            // Trigger APF logic with a small delay to ensure fields are rendered
            setTimeout(function () {
                $(document.body).trigger('apf_buynow_init', [self.$drawerBody]);
            }, 50);

            // Second trigger as a fail-safe for slower rendering fields
            setTimeout(function () {
                $(document.body).trigger('apf_buynow_init', [self.$drawerBody]);
            }, 400);
        },

        closeDrawer: function () {
            this.$overlay.removeClass('active');
            this.$drawer.removeClass('active');
            this.$body.addClass('hpco-drawer-closing');

            var self = this;
            setTimeout(function () {
                self.$body.removeClass('hpco-drawer-open hpco-drawer-closing');
                if (!self.$drawer.hasClass('active')) {
                    // Clear ALL content placeholders to ensure next open is clean
                    self.$drawerBody.html('');
                    self.$drawer.find('.hpco-drawer__thumbnail').html('');
                    self.$drawer.find('.hpco-drawer__title').text('');
                    self.$drawer.find('.hpco-drawer__price').html('');
                }
            }, 400);
        }
    };

    $(document).ready(function () {
        QuickBuy.init();
    });

})(jQuery);
