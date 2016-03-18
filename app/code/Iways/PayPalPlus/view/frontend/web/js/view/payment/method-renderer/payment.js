/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        '//www.paypalobjects.com/webstatic/ppplus/ppplus.min.js'
    ],
    function (Component) {
        var paypalplusConfig = window.checkoutConfig.payment.iways_paypalplus_payment;
        return Component.extend({
            defaults: {
                template: 'Iways_PayPalPlus/payment',
                paymentExperience: paypalplusConfig.paymentExperience,
                mode: paypalplusConfig.mode,
                showPuiOnSandbox: paypalplusConfig.showPuiOnSandbox,
                showLoadingIndicator: paypalplusConfig.showLoadingIndicator,
                country: paypalplusConfig.country,
                language: paypalplusConfig.language,
            },
            /**
             * @function
             */
            initVars: function () {
                this.paymentExperience = paypalplusConfig ? paypalplusConfig.paymentExperience : '';
                this.mode = paypalplusConfig ? paypalplusConfig.mode : '';
                this.country = paypalplusConfig ? paypalplusConfig.country : '';
                this.language = paypalplusConfig ? paypalplusConfig.language : '';
                this.showPuiOnSandbox = paypalplusConfig ? paypalplusConfig.showPuiOnSandbox : '';
                this.showLoadingIndicator = paypalplusConfig ? paypalplusConfig.showLoadingIndicator : '';
            },
            /**
             * @returns {*|String}
             */
            canInitialise: function () {
                return this.paymentExperience;
            },

            /**
             * @override
             */
            initObservable: function () {
                this.initVars();
                return this;
            },
            initPayPalPlusFrame: function () {
                var self = this;
                PAYPAL.apps.PPP({
                    approvalUrl: self.paymentExperience,
                    placeholder: "ppplus",
                    mode: self.mode,
                    useraction: "commit",
                    buttonLocation: "outside",
                    showPuiOnSandbox: self.showPuiOnSandbox,
                    showLoadingIndicator: self.showLoadingIndicator,
                    country: self.country,
                    language: self.language
                });
            }
        });
    }
);