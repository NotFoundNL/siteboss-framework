<?php

//this file is published by the siteboss-framework package

return [
    /*
    |--------------------------------------------------------------------------
    | Socket type
    |--------------------------------------------------------------------------
    |
    | This option controls the socket which is used, which is unix, tcp or none.
    |
    */
    'socket_type' => env('CLAMAV_SOCKET_TYPE', 'tcp'),

    /*
    |--------------------------------------------------------------------------
    | Unix Socket
    |--------------------------------------------------------------------------
    | The location of the unix socket-file. For example
    | /run/clamav/clamd.sock

    */
    'unix_socket' => env('CLAMAV_UNIX_SOCKET', '/run/clamav/clamd.sock'),

    /*
    |--------------------------------------------------------------------------
    | TCP Socket
    |--------------------------------------------------------------------------
    | The URL of the tcp socket.
    */
    'tcp_socket' => env('CLAMAV_TCP_SOCKET', 'tcp://127.0.0.1:3310'),

    /*
    |--------------------------------------------------------------------------
    | Quarantine folder
    |--------------------------------------------------------------------------
    | The folder where the files are placed that are considered virusses.
    | Folder is relative to the storage_path
    |
    | Please note that this folder will also be used as a temporary folder
    | to place the files that are being scanned
    */
    'quarantine_folder' => env('CLAMAV_TCP_SOCKET', '/quarantine/'),
];
