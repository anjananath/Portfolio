/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'pointsredeem',
                component: 'Voilaah_PointsRedemption/js/view/payment/method-renderer/pointsredeem'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);