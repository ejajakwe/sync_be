<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['https://syncbe-production.up.railway.app', 'https://sync-fe-kappa.vercel.app'],

    'allowed_headers' => ['*'],

    'supports_credentials' => true,

];