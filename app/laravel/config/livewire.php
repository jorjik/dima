<?php

return [

    /*
    |---------------------------------------------------------------------------
    | Temporary File Uploads
    |---------------------------------------------------------------------------
    |
    | Override the default Livewire temporary file upload rules (12MB) to allow
    | larger video/audio uploads (200MB).
    |
    */

    'temporary_file_upload' => [
        'rules' => ['required', 'file', 'max:204800'], // 200 MB
    ],

];
