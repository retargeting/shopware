# Retargeting Tracker

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
Tested on Shopware 5.2.21 & 5.2.22

Shopware 5.2.20 CSRF token problem. Check UPGRADE-5.2.md [https://github.com/shopware/shopware/blob/5.2/UPGRADE-5.2.md]

### TODO
Installation via composer or via setup.sh file

