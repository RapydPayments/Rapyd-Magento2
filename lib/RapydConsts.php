<?php

namespace rapyd\rapydmagento2\lib;

class RapydConsts
{
    public const RAPYD_TOOLKIT_JS_URL_PROD = 'https://checkouttoolkit.rapyd.net';
    public const RAPYD_TOOLKIT_JS_URL_TEST = 'https://sandboxcheckouttoolkit.rapyd.net';
    public const RAPYD_PLUGIN_URL_PROD = 'https://plugins.rapyd.net';
    public const RAPYD_PLUGIN_URL_TEST = 'https://sandboxplugins.rapyd.net';

    public const RAPYD_CATEGORIES_PATH = '/v1/plugins/magento/payments/categories';
    public const RAPYD_REDIRECT_PATH = '/v1/plugins/magento/payments/toolkit';

    public const RAPYD_THANKYOU_PAGE_SUCCESS = 'Thank you for your order.';
    public const RAPYD_THANKYOU_PAGE_ON_HOLD = 'Final confirmation for your payment will be arriving soon. Look for updates regarding your payment status.';
    public const RAPYD_THANKYOU_PAGE_ON_ERROR = 'Sorry, something is wrong with your payment. Please go back to recheck payment information and try again.';
    public const RAPYD_THANKYOU_PAGE_ON_CANCEL = 'Your payment was cancelled.';

    public const RAPYD_THANKYOU_PAGE_SUCCESS_TITLE = 'Your payment was successful';
    public const RAPYD_THANKYOU_PAGE_ON_HOLD_TITLE = 'Your payment is on hold';
    public const RAPYD_THANKYOU_PAGE_ON_ERROR_TITLE = 'There is an issue with your payment';
    public const RAPYD_THANKYOU_PAGE_ON_CANCEL_TITLE = 'There is an issue with your payment';
    public const RAPYD_ERROR_LOADING_TOOLKIT = 'Sorry, something is wrong with your payment. Please go back to recheck payment information and try again.';
}
