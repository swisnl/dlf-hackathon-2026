<?php

declare(strict_types=1);

return [
    'fields' => [
        'created_at' => 'Created at',
        'deleted_at' => 'Deleted at',
        'updated_at' => 'Updated at',
    ],
    'user' => [
        'fields' => [
            'email' => 'Email',
            'name' => 'Name',
            'password' => 'Password',
            'role' => 'Role',
        ],
        'helpers' => [
            'password' => 'Leave empty to keep the current password.',
        ],
        'plural' => 'Users',
        'singular' => 'User',
    ],
];
