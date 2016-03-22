/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'underscore',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        '//www.paypalobjects.com/webstatic/ppplus/ppplus.min.js'
    ],
    function (ko, $, Component, quote, paymentService, selectPaymentMethod, checkoutData) {
        var paypalplusConfig = window.checkoutConfig.payment.iways_paypalplus_payment;
        return Component.extend({
            isPaymentMethodSelected: ko.observable(false),
            lastCall: 'none',
            selectedMethod: "iways_paypalplus_payment",
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
                this.paymentCodeMappings = {};
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
                    self.selectPaymentMethod();
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
                        thirdPartyPaymentMethods: self.getThirdPartyPaymentMethods(),
                        onThirdPartyPaymentMethodSelected:function (data) {
                            self.lastCall = 'onThirdPartyPaymentMethodSelected';
                            self.selectedMethod = self.paymentCodeMappings[data.thirdPartyPaymentMethod];
                        },
                        enableContinue: function () {
                            if(self.lastCall != 'onThirdPartyPaymentMethodSelected') {
                                self.selectedMethod = 'iways_paypalplus_payment';
                            }
                            self.lastCall = 'enableContinue';
                            self.isPaymentMethodSelected = true;
                        },
                        disableContinue: function() {
                            self.isPaymentMethodSelected = false;
                        }
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
                            self.paymentCodeMappings[self.thirdPartyPaymentMethods[activeMethod.method].methodName] = activeMethod.method;
                            pppThirdPartyMethods.push(self.thirdPartyPaymentMethods[activeMethod.method]);
                        }
                    } catch (e) {
                        console.log(e);
                    }
                });
                return pppThirdPartyMethods;
            },
            /**
             * Get payment method data
             */
            getData: function() {
                var self = this;
                return {
                    "method": self.selectedMethod,
                    "po_number": null,
                    "additional_data": null
                };
            },
            placePPPOrder: function (data, event) {
                return this.placeOrder(data, event);
            }
        });
    }
);
