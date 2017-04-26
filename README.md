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
This work is licensed under a **Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0) License** because I couldn't find a license with an even longer name.

### Dependencies
Tested on Shopware 5.2.21

Shopware 5.2.20 CSRF token problem. Check UPGRADE-5.2.md [https://github.com/shopware/shopware/blob/5.2/UPGRADE-5.2.md]

### TODO
Installation via composer or via setup.sh file

