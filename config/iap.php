<?php

declare(strict_types=1);

return [
    /*
     * The expected audience claim for IAP JWT tokens.
     * For App Engine: /projects/PROJECT_NUMBER/apps/PROJECT_ID
     * For other services: /projects/PROJECT_NUMBER/global/backendServices/SERVICE_ID
     *
     * Leave null to skip audience validation.
     */
    'audience' => env('IAP_AUDIENCE'),
];
