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
        rendererList.removeAll();
        rendererList.push(
            {
                type: 'iways_paypalplus_payment',
                component: 'Iways_PayPalPlus/js/view/payment/method-renderer/payment'
            }
        );

        return Component.extend({});
    }
);