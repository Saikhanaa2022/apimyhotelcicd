<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */
    'base_auth' => [
        'client_id' => env('CLIENT_ID'),
        'client_secret' => env('CLIENT_SECRET'),
    ],
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'dashboard' => [
        'baseUrl' => env('BASE_URL', ''),
        'passwordResetUrl' => env('PASSWORD_RESET_URL', ''),
        'loginUrl' => env('LOGIN_URL', 'https://rms.myhotel.mn/login'),
        'activateUrl' => env('ACTIVATE_URL', ''),
        'emailVerifyUrl' => env('EMAIL_VERIFY_URL', ''),
    ],

    'artLab' => [
        'url' => env('VAT_URL'),
        'username' => env('VAT_USERNAME'),
        'password' => env('VAT_PASSWORD'),
    ],

    'lambda' => [
        'baseUrl' => env('WUBOOK_LAMBDA_URL'),
    ],

    'ihotel' => [
        'baseUrl' => env('IHOTEL_BASE_URL'),
    ],

    'ctrip' => [
        'baseUrl' => env('CTRIP_BASE_URL'),
        'baseUrlRoom' => env('CTRIP_BASE_URL_ROOM'),
    ],

    // Payment Terminals
    'golomt' => [
        'url' => env('GOLOMT_URL'),
        'key_number' => env('GOLOMT_KEY_NUMBER'),
    ],

    'capitron_base_url' => env('CAPITRON_BASE_URL'),
    'trans_base_url' => env('TRANS_BASE_URL'),

    'lendmn' => [
        'url' => env('LENDMN_BASE_URL'),
        'token' => env('LENDMN_TOKEN'),
        'client_id' => env('LENDMN_CLIENT_ID'),
        'client_secret' => env('LENDMN_CLIENT_SECRET'),
    ],

    'qpay' => [
        'account' => env('QPAY_ACCOUNT'),
        'merchant_code' => env('QPAY_MERCHANT_CODE'),
        'merchant_verification_code' => env('QPAY_MERCHANT_VERIFICATION_CODE'),
        'merchant_customer_code' => env('QPAY_MERCHANT_CUSTOMER_CODE'),
        'operator_code' => env('QPAY_OPERATOR_CODE'),
        'invoice_code' => env('QPAY_INVOICE_CODE'),
        'language_code' => env('QPAY_LANGUAGE_CODE'),
        'generate_invoice_url' => env('QPAY_GENERATE_INVOICE_URL'),
        'check_payment_url' => env('QPAY_CHECK_PAYMENT_URL'),
        'client_id' => env('QPAY_CLIENT_ID'),
        'client_secret' => env('QPAY_CLIENT_PASSWORD'),
        'base_url' => env('QPAY_BASE_URL'),
        'account_v2' => env('QPAY_ACCOUNT_V2')
    ],

    'mongolchat' => [
        'app_secret' => env('MC_APP_SECRET'),
        'api_key' => env('MC_API_KEY'),
        'worker_auth' => env('MC_WORKER_AUTH'),
        'branch_no' => env('MC_BRANCH_NO'),
        'base_url' => env('MC_BASE_URL'),
        'prefix' => env('MC_PREFIX'),
    ],

    'xroom' => [
        'api' => env('XROOM_API'),
        'access_key' => env('XROOM_TOKEN'),
        'qpay' => [
            'base_url' => env('XROOM_QPAY_BASE_URL'),
            'basic_auth' => env('XROOM_QPAY_BASIC_AUTH'),
            'invoice_code' => env('XROOM_QPAY_INVOICE_CODE')
        ],
        'socialpay' => [
            'base_url' => env('XROOM_SOCIALPAY_BASE_URL'),
            'key_number' => env('XROOM_SOCIALPAY_KEY_NUMBER'),
        ],
        'khanbank' => [
            'base_url' => env('XROOM_KHANBANK_BASE_URL'),
            'basic_auth' => env('XROOM_KHANBANK_BASIC_AUTH'),
            'account' => env('XROOM_KHANBANK_ACCOUNT'),
            'login_name' => env('XROOM_KHANBANK_LOGIN_NAME'),
            'tran_password' => env('XROOM_KHANBANK_TRAN_PASSWORD'),
        ]
    ]
];