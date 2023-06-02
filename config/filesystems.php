<?php

// duplicate entry in bootstrap/app.php/ config/logging.php
app()->useStoragePath($app->basePath(env('APP_STORAGE_PATH', '../storage')));

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'site' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'formbuilder' => [
            'driver' => 'local',
            'root' => storage_path('app/forms/'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => '/data',
            'visibility' => 'public',
        ],

        'private' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'url' => '/data',
            'visibility' => 'private',
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
        public_path('assets/static') => '../../../site/static',
        public_path('assets/public') => '../../../storage/app/public',
    ],

];
