<?php

return [
    'auth_on'           => 1,
    'auth_type'         => 1,
    'auth_group'        => 'admin_role',
    'auth_group_access' => 'admin_user_role',
    'auth_rule'         => 'admin_auth',
    'auth_user'         => 'admin_user',
    'auth_node'         => 'auth',
    
    'session' => [
        'admin_user_id'   => 'admin_user_id',
        'admin_username'  => 'admin_username',
        'admin_realname'  => 'admin_realname',
    ],
    
    'password' => [
        'salt'     => '',
        'hashalgo' => 'md5',
    ],
    
    'allow_fields' => [
        'username',
        'realname',
        'phone',
        'email',
        'status',
    ],
];
