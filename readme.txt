=== LTL Freight Quotes - XPO Edition ===
Contributors: enituretechnology
Tags: eniture,XPO,,LTL freight rates,LTL freight quotes, shipping estimates
Requires at least: 6.4
Tested up to: 6.6.2
Stable tag: 4.3.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Real-time LTL freight quotes from XPO Logistics. Fifteen day free trial.

== Description ==

XPO Logistics (NYSE: XPO) is a leading global provider of transportation and logistics solutions. The application retrieves your negotiated XPO LTL freight rates, takes action on them according to the application settings, and displays the result as shipping charges in the Shopify checkout process. If you don’t have an XPO Logistics account, use its [Open New Account](http://xpologistics.com/contact-us/customer-open-account)form to begin the process.

**Key Features**

* Displays negotiated LTL shipping rates in the shopping cart.
* Provides quotes for shipments within the United States and to Canada.
* Custom label results displayed in the shopping cart.
* Display transit times with returned quotes.
* Product specific freight classes.
* Support for variable products.
* Define multiple warehouses.
* Identify which products drop ship from vendors.
* Product specific shipping parameters: weight, dimensions, freight class.
* Option to determine a product's class by using the built in density calculator.
* Option to include residential delivery fees.
* Option to include fees for lift gate service at the destination address.
* Option to mark up quoted rates by a set dollar amount or percentage.
* Works seamlessly with other quoting apps published by Eniture Technology.

**Requirements**

* WooCommerce 6.4 or newer.
* A XPO Logistics Customer Account Number.
* Your username and password to XPO Logistics online shipping system.
* A API key from Eniture Technology.

== Installation ==

**Installation Overview**

Before installing this plugin you should have the following information handy:

* Your XPO Logistics Customer Account Number.
* Your username and password to XPO Logistics online shipping system.

If you need assistance obtaining any of the above information, contact your local XPO Logistics office
or call the [XPO Logistics](http://www.xpologistics.com) corporate headquarters at 800-755-2728.

A more comprehensive and graphically illustrated set of instructions can be found on the *Documentation* tab at
[eniture.com](https://eniture.com/woocommerce-xpo-ltl-freight/).

**1. Install and activate the plugin**
In your WordPress dashboard, go to Plugins => Add New. Search for "LTL Freight Quotes - XPO Edition", and click Install Now.
After the installation process completes, click the Activate Plugin link to activate the plugin.

**2. Get a API key from Eniture Technology**
Go to [Eniture Technology](https://eniture.com/woocommerce-xpo-ltl-freight/) and pick a
subscription package. When you complete the registration process you will receive an email containing your API key and
your login to eniture.com. Save your login information in a safe place. You will need it to access your customer dashboard
where you can manage your API keys and subscriptions. A credit card is not required for the free trial. If you opt for the free
trial you will need to login to your [Eniture Technology](http://eniture.com) dashboard before the trial period expires to purchase
a subscription to the API key. Without a paid subscription, the plugin will stop working once the trial period expires.

**3. Establish the connection**
Go to WooCommerce => Settings => XPO Logistics. Use the *Connection* link to create a connection to your XPO Freight
account.

**5. Select the plugin settings**
Go to WooCommerce => Settings => XPO Logistics. Use the *Quote Settings* link to enter the required information and choose
the optional settings.

**6. Enable the plugin**
Go to WooCommerce => Settings => Shipping. Click on the link for XPO Logistics and enable the plugin.

**7. Configure your products**
Assign each of your products and product variations a weight, Shipping Class and freight classification. Products shipping LTL freight should have the Shipping Class set to “LTL Freight”. The Freight Classification should be chosen based upon how the product would be classified in the NMFC Freight Classification Directory. If you are unfamiliar with freight classes, contact the carrier and ask for assistance with properly identifying the freight classes for your  products. 

== Frequently Asked Questions ==

= What happens when my shopping cart contains products that ship LTL and products that would normally ship FedEx or UPS? =

If the shopping cart contains one or more products tagged to ship LTL freight, all of the products in the shopping cart 
are assumed to ship LTL freight. To ensure the most accurate quote possible, make sure that every product has a weight, dimensions and a freight classification recorded.

= What happens if I forget to identify a freight classification for a product? =

In the absence of a freight class, the plugin will determine the freight classification using the density calculation method. To do so the products weight and dimensions must be recorded. This is accurate in most cases, however identifying the proper freight class will be the most reliable method for ensuring accurate rate estimates.

= Why was the invoice I received from XPO Logistics more than what was quoted by the plugin? =

One of the shipment parameters (weight, dimensions, freight class) is different, or additional services (such as residential 
delivery, lift gate, delivery by appointment and others) were required. Compare the details of the invoice to the shipping 
settings on the products included in the shipment. Consider making changes as needed. Remember that the weight of the packaging 
materials, such as a pallet, is included by the carrier in the billable weight for the shipment.

= How do I find out what freight classification to use for my products? =

Contact your local XPO Logistics office for assistance. You might also consider getting a subscription to ClassIT offered 
by the National Motor Freight Traffic Association (NMFTA). Visit them online at classit.nmfta.org.

= How do I get a XPO Logistics account? =

Check your phone book for local listings or call  800-755-2728.

= Where do I find my XPO Logistics username and password? =

Usernames and passwords to XPO Logistics online shipping system are issued by XPO Logistics. If you have a XPO Logistics account number, go to [xpologistics.com](http://www.xpologistics.com/) and click the login link at the top right of the page and choose Customer and then Less-Than-Truckload. You will be redirected to a page where you can register as a new user. If you don’t have a XPO Logistics account, contact the XPO Logistics at 800-755-2728.

= How do I get a API key for my plugin? =

You must register your installation of the plugin, regardless of whether you are taking advantage of the trial period or 
purchased a API key outright. At the conclusion of the registration process an email will be sent to you that will include the 
API key. You can also login to eniture.com using the username and password you created during the registration process 
and retrieve the API key from the My API keys tab.

= How do I change my plugin API key from the trail version to one of the paid subscriptions? =

Login to eniture.com and navigate to the My API keys tab. There you will be able to manage the licensing of all of your 
Eniture Technology plugins.

= How do I install the plugin on another website? =

The plugin has a single site API key. To use it on another website you will need to purchase an additional API key. 
If you want to change the website with which the plugin is registered, login to eniture.com and navigate to the My API keys tab. 
There you will be able to change the domain name that is associated with the API key.

= Do I have to purchase a second API key for my staging or development site? =

No. Each API key allows you to identify one domain for your production environment and one domain for your staging or 
development environment. The rate estimates returned in the staging environment will have the word “Sandbox” appended to them.

= Why isn’t the plugin working on my other website? =

If you can successfully test your credentials from the Connection page (WooCommerce > Settings > XPO Logistics > Connections) 
then you have one or more of the following licensing issues:

1) You are using the API key on more than one domain. The API keys are for single sites. You will need to purchase an additional API key.
2) Your trial period has expired.
3) Your current API key has expired and we have been unable to process your form of payment to renew it. Login to eniture.com and go to the My API keys tab to resolve any of these issues.

== Screenshots ==

1. Quote settings page
2. Warehouses and Drop Ships page
3. Quotes displayed in cart

== Changelog ==

= 4.3.5 =
* Fix: Resolved an issue with picking the freight class value for variant products.

= 4.3.4 =
* Update:Introduced "Restrict to State" shipping rule

= 4.3.3 =
* Update: Updated connection tab according to WordPress requirements 

= 4.3.2 =
* Update:Introduced a new hook used by the Microwarehouse add-on plugin

= 4.3.1 =
* Update: Introduced capability to suppress parcel rates once the weight threshold has been reached.
* Update: Compatibility with WordPress version 6.5.2
* Update: Compatibility with PHP version 8.2.0
* Fix:  Incorrect product variants displayed in the order widget.

= 4.3.0 =
* Update: Display “Free Shipping” at checkout when handling fee in the quote settings is -100% .
* Update: Introduced the Shipping Logs feature.
* Update: Introduced “product level markup” and “origin level markup”.

= 4.2.7 =
* Update: Introduced a field for the maximum weight per handling unit.
* Update: Updated the description text in the warehouse.

= 4.2.6 =
* Update: Changed required plan from standard to basic for delivery estimate options. 

= 4.2.5 =
* Update: Compatibility with WooCommerce HPOS(High-Performance Order Storage) 

= 4.2.4 =
* Update: Modified expected delivery message at front-end from “Estimated number of days until delivery” to “Expected delivery by”.
* Fix: Inherent Flat Rate value of parent to variations.
* Fix: Fixed space character issue in city name. 

= 4.2.3 =
* Update: Added compatibility with "Address Type Disclosure" in Residential address detection 

= 4.2.2 =
* Update: Included XPO access token in Test Connection.

= 4.2.1 =
* Update: Compatibility with WordPress version 6.1
* Update: Compatibility with WooCommerce version 7.0.1

= 4.2.0 =
* Update: Introduced connectivity from the plugin to FreightDesk.Online using Company ID

= 4.1.7 =
* Update: Compatibility with WordPress multisite network
* Fix: Fixed support link. 

= 4.1.6 =
* Update: Compatibility with PHP version 8.1.
* Update: Compatibility with WordPress version 5.9. 

= 4.1.5 =
* Fixed: Fixed inheritance of product freight class onto its variant.

= 4.1.4 =
* Update: Compatibility with preferred origin custom work.
* Fix: Fixes for PHP version 8.0.

= 4.1.3 =
* Update: Relocation of NMFC Number field along with freight class.

= 4.1.2 =
* Fix: Fixed XPO handling of XPO response.

= 4.1.1 =
* Update: Introduced headings to Parcel Boxing Properties and LTL Handling Unit Properties.
* Fix: Corrected issue with shipment going US to Canada.

= 4.1.0 =
* Update: Added features, Multiple Pallet Packaging and data analysis.

= 4.0.1 =
* Fix: Errors with with PHP version 8.0.

= 4.0.0 =
* Update: Compatibility with PHP version 8.0.
* Update: Compatibility with WordPress version 5.8.
* Fix: Corrected product page URL in connection settings tab.

= 3.0.0 =
* Update: Added feature "Weight threshold limit".
* Update: Added feature In-store pickup with terminal information.

= 2.7.0 =
* Update: Cuttoff Time.
* Update: Added images URL for freightdesk.online portal.
* Update: CSV columns updated.
* Update: Compatibility with Micro-warehouse addon.
* Update: Pallet packaging.
* Update: Virtual product details added in order meta data.

= 2.6.1 =
* Update: Introduced new features, Compatibility with WordPress 5.7, Order detail widget for draft orders, improved order detail widget for Freightdesk.online, compatibly with Shippable add-on, compatibly with Account Details(ET) add-don(Capturing account number on checkout page).

= 2.6.0 =
* Update: Compatibility with WordPress 5.6

= 2.5.3 = 
* Update: This update introduces: 1) Product nesting feature. 2) CSV export of microwarehouse. 3) Fixed In Store and Local delivery as an default selection.

= 2.5.2 =
* Update: Minimum country and state require to trigger quotes request

= 2.5.1 =
* Update: Compatibility with WordPress 5.5

= 2.5.0 =
* Update: Compatibility with shipping solution freightdesk.online

= 2.4.5 =
* Update: Ignore items with given Shipping Class(es).

= 2.4.4 =
* Update: Compatibility with WordPress 5.4

= 2.4.3 = 
* Update: Introduced XPO account type[Sender/Receiver] in account specific warehouse

= 2.4.2 = 
* Fix: Fixed php warranings  
 
= 2.4.1 = 
* Fix: Surcharges fee was not being applied in some cases 

= 2.4.0 = 
* Update: Introduced XPO account specific warehouse

= 2.3.1 = 
* Update: Changed UI of quote settings tab.

= 2.3.0 = 
* Update: This update introduces: 1) An option to customize "Cart Weight Threshold". 2) Don't sort by price. 3) Customizable error message in the event the plugin is unable to retrieve rates. 4) Changed labels of test connection.

= 2.2.2 = 
* Update: Introduced a second handling fee / markup field.

= 2.2.1 = 
* Fix: Fixed compatibility issue with Eniture Technology Small Package plugins.

= 2.2.0 = 
* Update: Introduced new feature "Weight of Handling Unit"

= 2.1.0 = 
* Update: Introduced settings to control quotes sorting on frontend

= 2.0.8 = 
* Update: Introduced settings for frontend message when shipping cannot be calculated 

= 2.0.7 =
* Fix: Removed repeated shipping option in case of Hold At Terminal  

= 2.0.6 =
* Fix: Conflict of order detail widget with WooCommerce

= 2.0.5 =
* Fix: Auto Detect Residential label

= 2.0.4 =
* Update: Introduced new Hold At Terminal 

= 2.0.3 =
* Update: Compatibility with WordPress 5.1

= 2.0.2 =
* Fix: Identify one warehouse and multiple drop ship locations in basic plan.

= 2.0.1 =
* Fix: lift gate delivery as option.

= 2.0.0 =
* Update: Introduced new features and Basic, Standard and Advanced plans.

= 1.3.1 =
* Update: Compatibility with WordPress 5.0

= 1.3.0 =
* Update: Introduced compatibility with the Residential Address Detection plugin.

= 1.2.2 =
* Fix: Corrected user guide link.

= 1.2.1 =
* Fix: Fixed issue with new reserved word in PHP 7.1.

= 1.2.0 =
* Update: Compatibility with WordPress 4.9

= 1.1.0 =
* Update: Added support for XPO Third Party Account Numbers.

= 1.0.1 =
* Update: Compatibility with WooCommerce 3.0

= 1.0 =
* Initial release.

== Upgrade Notice ==
