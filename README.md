![Retargeting.Biz Logo](https://s3.amazonaws.com/techpluto/wp-content/uploads/2017/06/29185746/techp_1194697.png)

# Retargeting Tracker plugin for Shopware 5.2.2.x & 5.3.x
Retargeting Tracker plugin installs the required tagging for [Retargeting.Biz](https://Retargeting.biz)'s features in Shopware based online shops, providing your eCommerce business with all the necessary tools to build a strong conversion rate optimization strategy.

In order to implement the Retargeting Tracker plugin you need to setup a [Retargeting.Biz](https://Retargeting.biz) account.

### Installation
```shell
git clone git@github.com:retargeting/shopware.git
```
or
```shell
git clone https://github.com/retargeting/shopware.git
```
```shell
mv shopware/ Retargeting
mkdir Frontend
mv shopware/ Frontend
zip -r Retargeting.zip Frontend/
```
### Configuration
* Go to your backend shop
* Configuration -> Plugin Manager -> Installed -> Upload plugin
* Upload Retargeting.zip
* Install Retargeting plugin
* Activate Retargeting plugin
* Enter your Tracking API KEY & REST API KEY
### License
This work is licensed under a **http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)**.

### Dependencies
Tested on Shopware 5.2.2x & 5.3.x

Shopware 5.2.20 CSRF token problem. Check UPGRADE-5.2.md [https://github.com/shopware/shopware/blob/5.2/UPGRADE-5.2.md]

### TODO
Installation via composer or via setup.sh file

