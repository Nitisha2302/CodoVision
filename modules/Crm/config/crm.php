<?php

return [
    'name' => 'CodoVision CRM',
    'route_prefix' => 'crm',
    'guard' => 'crm',

    'pipeline_statuses' => [
        'new' => 'New',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'requirement_received' => 'Requirement Received',
        'proposal_sent' => 'Proposal Sent',
        'negotiation' => 'Negotiation',
        'won' => 'Won',
        'lost' => 'Lost',
    ],

    'assignment' => [
        'mode' => env('CRM_ASSIGNMENT_MODE', 'manual'), // manual|round_robin
    ],

    /*
    | Mailbox credentials stay server-side only (Phase 3+).
    | Never expose these values to views or API responses.
    */
    'mail' => [
        'smtp' => [
            'host' => env('CRM_MAIL_HOST', env('MAIL_HOST')),
            'port' => env('CRM_MAIL_PORT', env('MAIL_PORT', 587)),
            'username' => env('CRM_MAIL_USERNAME', env('MAIL_USERNAME')),
            'password' => env('CRM_MAIL_PASSWORD', env('MAIL_PASSWORD')),
            'encryption' => env('CRM_MAIL_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls')),
            'from_address' => env('CRM_MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
            'from_name' => env('CRM_MAIL_FROM_NAME', env('MAIL_FROM_NAME', 'CodoVision CRM')),
        ],
        'imap' => [
            // Do not fall back to SMTP host — IMAP needs its own host (e.g. imap.titan.email).
            'host' => env('CRM_IMAP_HOST'),
            'port' => env('CRM_IMAP_PORT', 993),
            'username' => env('CRM_IMAP_USERNAME', env('MAIL_USERNAME')),
            'password' => env('CRM_IMAP_PASSWORD', env('MAIL_PASSWORD')),
            'encryption' => env('CRM_IMAP_ENCRYPTION', 'ssl'),
            'validate_cert' => env('CRM_IMAP_VALIDATE_CERT', true),
        ],
        'sync_limit' => (int) env('CRM_MAIL_SYNC_LIMIT', 40),
    ],
];
