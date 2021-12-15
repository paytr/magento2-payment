# About PayTR
PAYTR, is a service which provides the website owners to get secured online payments from the websites in a fast and simple way with the help of PCI-DSS Level 1 Service Provider adapted technical infrastructure and experienced staff and also has the licence of the Central Bank of Turkish Republic. PayTR provides both the store’s payment security and the customer's card security with its PCI-DSS Level 1 Service Provider adapted technical infrastructure by its experienced staff.

## PayTR - Magento 2 Virtual Pos Payment Gateway

The sellers who use PayTR Virtual Pos payment methods add-on provide the customers to choose PayTR payment method on the checkout page. The sellers who use PayTR payment methods provide to take secured payments from PayTR services. Customers using the PayTR payment method allow them to pay securely through the store's PayTR services. Customers can pay with multiple cards such as American Express, Visa Electron, Visa Debit, MasterCard, MasterCard Debit, etc. The module allows customers to pay with single payment or installment options. After the customer makes the payment, the amount paid is transferred to the seller's PayTR account.

## Add-on Settings
The sellers can go to PayTR Virtual Pos add-on by clicking the payment method link after they have logged in to Magento Admin Panel. PayTR Virtual Pos add-on settings are done on this page. 

Copy MERCHANT ID, MERCHANT KEY and MERCHANT SALT values from PayTR Merchant Panel page, then paste them on a related place in PayTR Virtual Pos add-on. 

### How To Use PayTR Virtual Pos Add-on To Receive Payment ###
* The customers move on to the payments step after they have added the products which they have chosen from the seller’s store to the shopping cart.
* The customers move on by choosing PayTR Virtual Pos payment method.
* PayTR service brings a PCI-DSS Level 1 Service Provider adapted and SSL licenced checkout page to the customer’s screen. 
* The customers complete their payments on this page by typing their card details. 
* The customers are directed to the page that will be only directed in case of the payment is succeeded after the completion of payment. 
* PayTR service sends a notification regarding that the operation has succeeded to PayTR Virtual Pos add-on and updates the order status. 

## Requirements
This plugin supports Magento2 version 
* 2.3.0 and higher
* 2.4.0 and higher

## Installation
You can install our plugin through Composer:
```
composer require paytr/magento2-payment
php bin/magento module:enable Paytr_Payment --clear-static-content
bin/magento setup:upgrade
```
For more information see our [User Guides](https://marketplace.magento.com/media/catalog/product/paytr-payment-1-1-1-ce/user_guides.pdf)

## License
MIT license. For more information, see the LICENSE file.