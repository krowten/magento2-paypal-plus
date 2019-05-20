#  Magento 2 PayPal PLUS

PayPal PLUS is a solution where PayPal offers PayPal, Credit Card, Direct Debit (ELV) and Pay Upon Invoice (Kauf auf Rechnung) as individual payment options on the payment selection page. These payment methods cover around 80% customer demand in Germany.

No matter how customer chooses to pay, it is always a single PayPal transaction for merchant, including all resulting advantages like Seller Protection and easy refund.

Based on the payment method selected by the buyer, he will be presented with either the PayPal Login page or an input mask for bank / credit card details. Should PayPal PLUS service be unavailable, standard Magento payment methods will be displayed.

## Installation

To install the Magento 2 PayPal PLUS extension please add our repository to your Magento _composer.json_.

    {
        "repositories": [
                {
                    "url": "git@github.com:i-ways/magento2-paypal-plus.git",
                    "type": "git"
                }
            ]
    }

After you added our repository you need to require our module.

There are to possibilities:

1. Run the command _composer require iways/module-pay-pal-plus_
2. Add it manually to your _composer.json_


    "require": {
           "iways/module-pay-pal-plus": "~1.0"
    }

## Enable our module in Magento

To enable our module via Magento 2 CLI go to your Magento root and run:

    bin/magento module:enable --clear-static-content Iways_PayPalPlus


To initialize the Database updates you must run following command afterwards:

    bin/magento setup:upgrade

The Magento 2 PayPal PLUS module should now be installed and ready to use.

## Issues
Please use our Servicedesk at: https://support.i-ways.net/hc/de
