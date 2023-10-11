<p align="center">
  <img src="https://camo.githubusercontent.com/517483ae0eaba9884f397e9af1c4adc7bbc231575ac66cc54292e00400edcd10/68747470733a2f2f7777772e6d756c7469736166657061792e636f6d2f66696c6561646d696e2f74656d706c6174652f696d672f6d756c7469736166657061792d6c6f676f2d69636f6e2e737667" width="400px" position="center">
</p>

# MultiSafepay module for Mirakl
**The module is currently in beta. Please use with caution.**

## About MultiSafepay ##
MultiSafepay is a collecting payment service provider which means we take care of the agreements, technical details and payment collection required for each payment method. You can start selling online today and manage all your transactions from one place.

## Supported Payment Methods ##
The supported Payment Methods for this module can be found over here: [Payment Methods & Giftcards](https://docs.multisafepay.com/plugins/magento2/faq/#available-payment-methods-in-magento-2)

This module does not work in combination with Magento Vault yet. Please disable Vault for any payment methods first, before using this module.

## Requirements
- To use the plugin you need a MultiSafepay account. You can create a test account on https://testmerchant.multisafepay.com/signup
- Magento Open Source version 2.4.x
- Mirakl
- PHP 7.4+

## Installation
This module can be installed via composer:

```bash
composer require multisafepay/magento2-mirakl
```

Next, enable the module:
```bash
php bin/magento module:enable MultiSafepay_Mirakl
```

Next, run the following commands:
```bash
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
```

## Configuration.

**In Mirakl:** 
- Browse to Settings > Advanced parameters > Stores > Custom Fields.
- Add a new section: MultiSafepay
- Add a new custom field using the following details: 
  - Section: MultiSafepay
  - Label: Merchant ID
  - Code: multisafepay-merchant-id
  - Type Text: 
  - Read Only
  - Required Field: yes. 
  - Value for existing stores, must be empty, but that value must be filled in the store configuration page.
- Browse to Settings > Platform > Technical Settings > System Integrations > Customer Debit and configure the debit connector with the following settings:
  - Transmission type: HTTP
  - URL: {OPERATOR-URL}/multisafepay/mirakl/debit
  - Method: POST
  - Content Type: Json
- Browse to Settings > Platform > Technical Settings > System Integrations > Customer Refund and configure the refund connector with the following settings:
    - Transmission type: HTTP
    - URL: {OPERATOR-URL}/multisafepay/mirakl/refund
    - Method: POST
    - Content Type: Json

**In Magento:**
- Browse to Stores > Settings > Configuration > MultiSafepay > Mirakl.
  - Fill the required data; according the environment previously setup in MultiSafepay general settings.
- Browse to Stores > Settings > Configuration > Mirakl > Connector.
  - On "Order Workflow" section, in field "Trigger on Statuses", select "Closed" and "Processing".

## Support
You can create issues on our repository. If you need any additional help or support, please contact <a href="mailto:integration@multisafepay.com">integration@multisafepay.com</a>

We are also available on our Magento Slack channel [#multisafepay-payments](https://magentocommeng.slack.com/messages/multisafepay-payments/).
Feel free to start a conversation or provide suggestions as to how we can refine our MageWire Checkout integration.

## A gift for your contribution
We look forward to receiving your input. Have you seen an opportunity to change things for better? We would like to invite you to create a pull request on GitHub.
Are you missing something and would like us to fix it? Suggest an improvement by sending us an [email](mailto:integration@multisafepay.com) or by creating an issue.

What will you get in return? A brand new designed MultiSafepay t-shirt which will make you part of the team!

## License
[Open Software License (OSL 3.0)](https://github.com/MultiSafepay/Magento2Msp/blob/master/LICENSE.md)

## Want to be part of the team?
Are you a developer interested in working at MultiSafepay? [View](https://www.multisafepay.com/careers/#jobopenings) our job openings and feel free to get in touch with us.
