# Simply Sync

## Description

Simply Sync is a simple API for synchronizing outside services with SimplyCast.com's CRM. Those services include but not limited to social media such as Facebook, Instagram, Twitter, etc. and CRM such as Salesforce.

The API makes use of public available REST API of each service/platform to retrieve and add contact data between the platforms.

## Prerequisite

This API requires the following libraries:

1. [SimplyCast PHP Wrapper](https://github.com/SimplyCast/php-wrapper)
2. [PHP](http://php.net/downloads.php) >= 5.6.*

## Current Supported Platforms

1. Act! Essential
2. Freshdesk
3. Salesforce CRM
4. Solve360
5. Tactile CRM

## How to use the API

```php
// Instantiate SimplySync object
$ss = new SimplySync();

// Setup SimplyCast API
$simplyCast = $ss->setupSimplyCast(SC_PUBLICKEY, SC_SECRETKEY);

// Setup CRM platform from which data to be synced
$ae = $ss->setup(
                    SimplySync::PLATFORM_ACTESSENTIAL,
                    array(
                        "apikey" => AE_APIKEY,
                        "devkey" => AE_DEVKEY,
                    )
                );

// Sync from the specified platform to SimplyCast's CRM
$ss->syncFrom(SimplySync::PLATFORM_ACTESSENTIAL);
```

## License & Authors

**Author:** Lengieng Ing (ing.lengieng@gmail.com)

This software is distributed under the MIT license. Please see the attached file called LICENSE.txt.
