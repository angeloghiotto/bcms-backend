<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
         * S3 / R2 Configuration
         * 
         * Supports both AWS S3 and Cloudflare R2 (S3-compatible API).
         * 
         * For R2, use these environment variables:
         * R2_ACCESS_KEY_ID=your_r2_access_key_id
         * R2_SECRET_ACCESS_KEY=your_r2_secret_access_key
         * R2_POSTS_BUCKET=your_posts_bucket_name (or R2_BUCKET for general bucket)
         * R2_ENDPOINT=https://your_account_id.r2.cloudflarestorage.com
         * R2_REGION=auto (or any value, R2 doesn't use regions)
         * R2_USE_PATH_STYLE_ENDPOINT=false
         * 
         * Alternatively, you can use AWS_ prefixed variables for compatibility.
         */
        's3' => [
            'driver' => 's3',
            'key' => env('R2_ACCESS_KEY_ID') ?: env('AWS_ACCESS_KEY_ID'),
            'secret' => env('R2_SECRET_ACCESS_KEY') ?: env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('R2_REGION') ?: env('AWS_DEFAULT_REGION', 'auto'),
            'bucket' => env('R2_POSTS_BUCKET') ?: env('R2_BUCKET') ?: env('AWS_BUCKET'),
            'url' => env('R2_URL') ?: env('AWS_URL'),
            'endpoint' => env('R2_ENDPOINT') ?: env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('R2_USE_PATH_STYLE_ENDPOINT') !== null ? (bool) env('R2_USE_PATH_STYLE_ENDPOINT') : (env('AWS_USE_PATH_STYLE_ENDPOINT', false)),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
