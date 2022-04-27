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
                type: 'nofraud',
                component: 'NoFraud_Checkout/js/view/payment/method-renderer/custompayment'
            }
        );
        return Component.extend({});
    }
);