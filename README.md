# Magento Google Analytics Extension

## Introduction

Extends the built-in Google Analytics module to convert transaction values to base currency.

This is important when using multiple stores with different currencies and the same Google Analytics profile.

## Changelog

* 0.9.1

    * Compatible with Magento CE 1.4.1.1+
    * Optimized Google Analytics asynchronous GA tracking snippet.

* 0.9.0

    * First public release. Compatible with Magento CE 1.4.x.

## Installation

1. Make sure you have [modman](https://github.com/colinmollenhour/modman) installed.

2. Install via modman (for details consult modman README):

        cd [magento root folder]
        modman init
        modman magento-google-analytics clone https://github.com/improove/magento-google-analytics.git

3. Make sure you've cleaned Magento's cache to enable the new module; hit refresh

## Feedback & questions

Found a bug or missing a feature? Don't hesitate to create a new issue here on [GitHub](https://github.com/improove/magento-google-analytics). Or contact me [directly](https://github.com/ptz0n).