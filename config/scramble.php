<?php

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    'api_path' => 'api',

    'api_domain' => null,

    'export_path' => 'storage/api-docs/api-docs.json',

    'info' => [
        'version' => env('APP_VERSION', '0.0.1'),
        'description' => 'OpenAPI documentation for Task Manager API.',
    ],

    'ui' => [
        'title' => 'Task Manager API Docs',
        'theme' => 'light',
        'hide_try_it' => false,
        'hide_schemas' => false,
        'logo' => '',
        'try_it_credentials_policy' => 'include',
        'layout' => 'responsive',
    ],

    'servers' => [
        'Production' => rtrim((string) env('APP_URL', 'http://localhost'), '/').'/api',
    ],

    'enum_cases_description_strategy' => 'description',

    'enum_cases_names_strategy' => false,

    'flatten_deep_query_parameters' => true,

    'access' => [
        'public' => env('SCRAMBLE_PUBLIC_DOCS', false),
        'allowed_emails' => env('SCRAMBLE_ALLOWED_EMAILS', ''),
    ],

    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    'extensions' => [],
];
