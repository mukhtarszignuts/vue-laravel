<?php

return [
    'ADMIN_EMAIL'       =>  env('ADMIN_EMAIL'), // Admin email address
    'ADMIN_PASSWORD'    =>  env('ADMIN_PASSWORD'), // Admin password

    'mail_bcc'          =>  env('MAIL_BCC'),

    'TEMP_PASSWORD'     =>  env('TEMP_PASSWORD'), // Admin password

    'FRONTEND_URL'                      => env('FRONTEND_URL'),
    'FRONTEND_FORGET_PASSWORD_URL'      => env('FRONTEND_FORGET_PASSWORD_URL'),
    
    'FRONTEND_VERIFICATION'             => 'email-verification',
    'FRONTEND_SURVEY'                   => 'survey',
    'reports_attachment_dir'            =>'reports/attachment',

   
    'PASSWORD_RESET_TYPE' => [
        'reset_password'    => 'RP', // Password reset type code for reset password
        'new_password'      => 'NP', // Password reset type code for new password
    ],

    'USER_ROLE' => [
        'admin'         => 'A', // Role code for admin users
        'manager'       => 'M', // Role code for manager users
        'customer'      => 'C', // Role code for customer users
    ]
];
