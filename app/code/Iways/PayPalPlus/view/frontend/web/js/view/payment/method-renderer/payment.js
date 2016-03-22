/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment-service',
        '//www.paypalobjects.com/webstatic/ppplus/ppplus.min.js'
    ],
    function ($, Component, quote, paymentService) {
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
                thirdPartyPaymentMethods: paypalplusConfig.thirdPartyPaymentMethods,
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
                this.thirdPartyPaymentMethods = paypalplusConfig ? paypalplusConfig.thirdPartyPaymentMethods : [];
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
                if(self.canInitialise()) {
                    this.selectPaymentMethod();
                    window.ppp = PAYPAL.apps.PPP({
                        approvalUrl: self.paymentExperience,
                        placeholder: "ppplus",
                        mode: self.mode,
                        useraction: "commit",
                        buttonLocation: "outside",
                        showPuiOnSandbox: self.showPuiOnSandbox,
                        showLoadingIndicator: self.showLoadingIndicator,
                        country: self.country,
                        language: self.language,
                        thirdPartyPaymentMethods: self.getThirdPartyPaymentMethods()
                    });
                }
            },
            getThirdPartyPaymentMethods: function() {
                var self = this;
                var activeMethods = paymentService.getAvailablePaymentMethods();
                var pppThirdPartyMethods = [];
                _.each(activeMethods, function(activeMethod) {
                    try {
                        if(self.thirdPartyPaymentMethods[activeMethod.method] !== undefined) {
                            pppThirdPartyMethods.push(self.thirdPartyPaymentMethods[activeMethod.method]);
                        }
                    } catch (e) {
                        console.log(e);
                    }
                });
                return pppThirdPartyMethods;
            }

        });
    }
);
