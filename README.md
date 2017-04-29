[![Build Status](https://scrutinizer-ci.com/g/gplcart/gplcart/badges/build.png?b=dev)](https://scrutinizer-ci.com/g/gplcart/gplcart/build-status/dev)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gplcart/gplcart/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/gplcart/gplcart/?branch=dev)

## WARNING. Dev branch in not for production. Please wait until the release of 1.X ##

## About ##
GPLCart is an open source e-commerce platform based on the classical LAMP stack (Linux+ Apache+Mysql+PHP). It's free, simple, modern and extensible solution that allows you to build online-shops fast and easy. GplCart is not a fork of an existing software. It's completely unique, made "with blood, sweat and tears" from the scratch.

## Requirements ##

- PHP 5.4+, Mysql 5+, Apache 1+

Also you'll need the following extension enabled:

- Curl
- OpenSSL
- Mb string
- Mod Rewrite
- ZipArchive

## Installation ##

Old school:

1. Download and extract to your hosting directory all files inside "gplcart" directory
2. Go to http://yourdomain.com and follow the instructions

Composer:

Clone to `test` directory

    composer create-project gplcart/gplcart test --stability dev

then you can perform full installation:

1. move to `test` directory `cd test`
2. run installation wizard `php gplcart install`

## Some key features ##

For developers:

- Simple MVC pattern
- PSR-0, PSR-4 standard compliance
- Dependency injection
- Minimum static methods
- Modules are damn simple, theme = module. [See how you can generate your module](https://github.com/gplcart/skeleton)
- Tons of hooks
- Command line support (extensible)
- Ability to rewrite almost any core method from a module (no monkey patching, "VQ mods")
- Supports any template engine, including [TWIG](https://github.com/gplcart/twig)
- Supports versioned dependencies for modules and 3-d party libraries

For owners:

- Really simple UI
- Faster than any other popular shopping cart
- Multistore `anotherstore.com, anotherstore.domain.com`
- International, [easy translatable](https://github.com/gplcart/extractor)
- Product comparison
- Wishlists even for anonymous
- Address books
- OAuth 2.0 support
- No stupid cart pages, just one checkout page
- True one page checkout with graceful degradation when JS is disabled
- Product classes
- Product fields (images, colors, text)
- Product combinations (XL + red, XL + green etc) with the easiest management you've ever seen
- Super flexible price rules both for catalog and checkout (including coupons)
- Roles and access control
- Backups
- Autogenerated URL aliases (with transliteration)
- Autogenerated SKU for every product combination (and they're unique per store)
- JS/CSS aggregation and compression

...and much more!
