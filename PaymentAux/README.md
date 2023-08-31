# Mage2 Module PaymentModel Checks

    ``paymentmodel/module-checks``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
creating new module

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/PaymentModel`
 - Enable the module by running `php bin/magento module:enable PaymentModel_Checks`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require paymentmodel/module-checks`
 - enable the module by running `php bin/magento module:enable PaymentModel_Checks`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications

 - Plugin
	- afterGetCountry - Magento\Payment\Model\Checks\CanUseForCountry > PaymentModel\Checks\Plugin\Magento\Payment\Model\Checks\CanUseForCountry


## Attributes



