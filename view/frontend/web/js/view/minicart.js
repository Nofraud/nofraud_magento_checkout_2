/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'sidebar',
    'mage/translate',
    'mage/dropdown'
], function (Component, customerData,  $, ko, _) {
    'use strict';

    var sidebarInitialized = false,
        addToCartCalls = 0,
        miniCart;

    miniCart = $('[data-block=\'minicart\']');

    /**
     * @return {Boolean}
     */
    function initSidebar() {
        if (miniCart.data('mageSidebar')) {
            miniCart.sidebar('update');
        }

        if (!$('[data-role=product-item]').length) {
            return false;
        }
        miniCart.trigger('contentUpdated');

        if (sidebarInitialized) {
            return false;
        }
        sidebarInitialized = true;
        miniCart.sidebar({
            'targetElement': 'div.block.block-minicart',
            'url': {
                'checkout': window.checkout.checkoutUrl,
                'update': window.checkout.updateItemQtyUrl,
                'remove': window.checkout.removeItemUrl,
                'loginUrl': window.checkout.customerLoginUrl,
                'isRedirectRequired': window.checkout.isRedirectRequired
            },
            'button': {
                'checkout': '#top-cart-btn-checkout',
                'remove': '#mini-cart a.action.delete',
                'close': '#btn-minicart-close'
            },
            'showcart': {
                'parent': 'span.counter',
                'qty': 'span.counter-number',
                'label': 'span.counter-label'
            },
            'minicart': {
                'list': '#mini-cart',
                'content': '#minicart-content-wrapper',
                'qty': 'div.items-total',
                'subtotal': 'div.subtotal span.price',
                'maxItemsVisible': window.checkout.minicartMaxItemsVisible
            },
            'item': {
                'qty': ':input.cart-item-qty',
                'button': ':button.update-cart-item'
            },
            'confirmMessage': $.mage.__('Are you sure you would like to remove this item from the shopping cart?')
        });
    }

    miniCart.on('dropdowndialogopen', function () {
        initSidebar();
    });

    $('body').on("click", '.iframebasedpayment', function() {
        var paymentName     = $(this).data('payment');
        var cartData        = customerData.get('cart');
        var customer        = customerData.get('customer');
        var cartId          = customerData.get('nofrudcheckout')().quote_id;
        var currencyCode    = customerData.get('nofrudcheckout')().currencycode;
        var languageCode    = customerData.get('nofrudcheckout')().languagecode;
        var storeCode       = customerData.get('nofrudcheckout')().storecode;

        if(cartId){
                $('[data-block="minicart"]').find('[data-role="dropdownDialog"]').dropdownDialog('close');
                var params = [];
                params['data-nf-access-token'] = customer().fullname && customer().firstname ? window.nofraudcheckout_accesstokenforcustomer : window.nofraudcheckout_accesstokenfornotlogin;
                params['data-nf-cart-id'] = cartId;
                params['data-nf-customer-is-logged-in'] = customer().fullname && customer().firstname ? 1 : 0;
                params['data-nf-merchant-id'] = window.nofraudcheckout_merchant;
                params['data-nf-store-url'] = BASE_URL;
                params['currencyCode'] = currencyCode;
                params['languageCode'] = languageCode;
                params['storeCode'] = storeCode;
                if(paymentName != null && typeof paymentName === 'string'){
                    params['paymentName'] = paymentName;
                }
                console.log(params);
                nfOpenCheckout(params);
        }     
    });


    return Component.extend({
        shoppingCartUrl: window.checkout.shoppingCartUrl,
        maxItemsToDisplay: window.checkout.maxItemsToDisplay,
        cart: {},

        // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        /**
         * @override
         */
        initialize: function () {
            var self = this,
                cartData = customerData.get('cart');
				
			var sections = ['cart'];
			customerData.invalidate(sections);
			customerData.reload(sections, true); 
			window.nofraudcheckout_firstload = 0;

		    cartData.subscribe(function (updatedCart) {
                addToCartCalls--;
                this.isLoading(addToCartCalls > 0);
                sidebarInitialized = false;
                this.update(updatedCart);
                initSidebar();
            }, this);

            $('[data-block="minicart"]').on('contentLoading', function () {
                addToCartCalls++;
                self.isLoading(true);
            });

            if (
                cartData().website_id !== window.checkout.websiteId && cartData().website_id !== undefined ||
                cartData().storeId !== window.checkout.storeId && cartData().storeId !== undefined
            ) {
                customerData.reload(['cart'], false);
            }
			
            return this._super();
        },
        //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

        isLoading: ko.observable(false),
        initSidebar: initSidebar,

        /**
         * Close mini shopping cart.
         */
        closeMinicart: function () {
	        $('[data-block="minicart"]').find('[data-role="dropdownDialog"]').dropdownDialog('close');
	    },

        /**
         *  Open iframe from the nofraud
         * @param paymentName
         */
        openPopup: function (paymentName=null) {
			var cartData        = customerData.get('cart');
			var customer        = customerData.get('customer');
            var cartId          = customerData.get('nofrudcheckout')().quote_id;
            var currencyCode    = customerData.get('nofrudcheckout')().currencycode;
            var languageCode    = customerData.get('nofrudcheckout')().languagecode;
            var storeCode       = customerData.get('nofrudcheckout')().storecode;

            if(cartId){
                //if(window.nofraudcheckout_firstload > 1) {
                    $('[data-block="minicart"]').find('[data-role="dropdownDialog"]').dropdownDialog('close');
                    var params = [];
                    params['data-nf-access-token'] = customer().fullname && customer().firstname ? window.nofraudcheckout_accesstokenforcustomer : window.nofraudcheckout_accesstokenfornotlogin;
                    params['data-nf-cart-id'] = cartId;
                    params['data-nf-customer-is-logged-in'] = customer().fullname && customer().firstname ? 1 : 0;
                    params['data-nf-merchant-id'] = window.nofraudcheckout_merchant;
                    params['data-nf-store-url'] = BASE_URL;
                    params['currencyCode'] = currencyCode;
                    params['languageCode'] = languageCode;
                    params['storeCode'] = storeCode;
                    if(paymentName != null && typeof paymentName === 'string'){
                        params['paymentName'] = paymentName;
                    }
                    console.log(params);
                    nfOpenCheckout(params);
                //}
			}
        },

        /**
         * @param {String} productType
         * @return {*|String}
         */
        getItemRenderer: function (productType) {
            return this.itemRenderer[productType] || 'defaultRenderer';
        },

        /**
         * Update mini shopping cart content.
         *
         * @param {Object} updatedCart
         * @returns void
         */
        update: function (updatedCart) {
            _.each(updatedCart, function (value, key) {
                if (!this.cart.hasOwnProperty(key)) {
                    this.cart[key] = ko.observable();
                }
                this.cart[key](value);
            }, this);
        },

        /**
         * Get cart param by name.
         *
         * @param {String} name
         * @returns {*}
         */
        getCartParamUnsanitizedHtml: function (name) {
            if (!_.isUndefined(name)) {
                if (!this.cart.hasOwnProperty(name)) {
                    this.cart[name] = ko.observable();
                }
            }
            return this.cart[name]();
        },

        /**
         * @deprecated please use getCartParamUnsanitizedHtml.
         * @param {String} name
         * @returns {*}
         */
        getCartParam: function (name) {
            return this.getCartParamUnsanitizedHtml(name);
        },

        /**
         * Returns array of cart items, limited by 'maxItemsToDisplay' setting
         * @returns []
         */
        getCartItems: function () {
            var items = this.getCartParamUnsanitizedHtml('items') || [];
            items = items.slice(parseInt(-this.maxItemsToDisplay, 10));
            return items;
        },

        /**
         * Returns count of cart line items
         * @returns {Number}
         */
        getCartLineItemsCount: function () {
            var items = this.getCartParamUnsanitizedHtml('items') || [];

            return parseInt(items.length, 10);
        },

        /**
        * Returns if NoFraud activate or deactivate
        * @returns {Number}
        */
        isNoFraudEnabled: function () {
            return customerData.get('nofrudcheckout')().isNofraudenabled;
        }
    });
});
