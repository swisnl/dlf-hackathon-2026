<?php

declare(strict_types=1);

return [
    'attempt' => [
        'fields' => [
            'exercise' => 'Exercise',
            'found' => 'Found',
            'gave_up' => 'Gave up',
            'number' => 'Attempt',
            'selected_lines' => 'Clicked lines',
            'stars' => 'Stars',
            'wrong' => 'Wrong',
        ],
        'plural' => 'Attempts',
        'singular' => 'Attempt',
    ],
    'fields' => [
        'created_at' => 'Created at',
        'deleted_at' => 'Deleted at',
        'updated_at' => 'Updated at',
    ],
    'student' => [
        'fields' => [
            'badges' => 'Badges',
            'badges_count' => 'Badges',
            'email' => 'Email',
            'exercises_count' => 'Exercises made',
            'name' => 'Name',
            'stars' => 'Stars',
        ],
        'placeholders' => [
            'badges' => 'No badges yet',
        ],
        'plural' => 'Students',
        'singular' => 'Student',
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
