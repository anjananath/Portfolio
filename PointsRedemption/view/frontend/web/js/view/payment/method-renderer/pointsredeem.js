/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Voilaah_PointsRedemption/payment/pointsredeem'
            },

            /** Returns send check to info */
            getMailingAddress: function() {
                console.log('--getMailingAddress--');
                return window.checkoutConfig.payment.pointsredeem.mailingAddress;
                //return 'john@change.me';
            },

            /** Returns payable to info */
            getPayableTo: function() {
                console.log('--getPayableTo--');
                return window.checkoutConfig.payment.pointsredeem.payableTo;
                //return 'John';
            }
        });
    }
);