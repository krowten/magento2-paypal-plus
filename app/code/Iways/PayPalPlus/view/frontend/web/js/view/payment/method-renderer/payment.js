/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'jquery',
        'underscore',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment-service',
        'Iways_PayPalPlus/js/action/patch-ppp-payment',
        'Magento_Checkout/js/model/payment/additional-validators',
        '//www.paypalobjects.com/webstatic/ppplus/ppplus.min.js'
    ],
    function (ko, $, _, Component, quote, paymentService, patchPPPPayment, additionalValidators) {
        var paypalplusConfig = window.checkoutConfig.payment.iways_paypalplus_payment;
        return Component.extend({
            isPaymentMethodSelected: ko.observable(false),
            lastCall: false,
            ppp: false,
            continueCount: 0,
            selectedMethod: "iways_paypalplus_payment",
            isInitialized: false,
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
                if(self.canInitialise() && !self.isInitialized) {
                    self.selectPaymentMethod();
                    self.ppp = PAYPAL.apps.PPP({
                        approvalUrl: self.paymentExperience,
                        placeholder: "ppplus",
                        mode: self.mode,
                        useraction: "commit",
                        buttonLocation: "outside",
                        showPuiOnSandbox: self.showPuiOnSandbox,
                        showLoadingIndicator: self.showLoadingIndicator,
                        country: self.country,
                        language: self.language,
                        preselection: "paypal",
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
                            $("#place-ppp-order").removeAttr("disabled");
                        },
                        disableContinue: function() {
                            self.isPaymentMethodSelected = false;
                            $("#place-ppp-order").attr("disabled", "disabled");
                        }
                    });
                    self.isInitialized = true;
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
                if (event) {
                    event.preventDefault();
                }
                var self = this;
                console.log("PlaceOrderMethod: " + self.selectedMethod);
                if(self.selectedMethod == "iways_paypalplus_payment") {
                    if (this.validate() && additionalValidators.validate()) {
                        patchPPPPayment(this.messageContainer, this.getData(), self.ppp);
                        return true;
                    }
                    return false;
                } else {
                    return this.placeOrder(data, event);
                }
            }
        });
    }
);
