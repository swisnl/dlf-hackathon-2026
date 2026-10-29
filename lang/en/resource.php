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
    'exercise' => [
        'actions' => [
            'close' => 'Close',
            'preview' => 'Preview',
        ],
        'fields' => [
            'attempts_count' => 'Attempts',
            'correct_code' => 'Correct class',
            'error_title' => 'Error title',
            'errors' => 'Errors',
            'explanation' => 'Explanation',
            'hint' => 'Hint',
            'line' => 'Line',
            'published' => 'Published',
            'stars' => 'Stars',
            'title' => 'Title',
            'topic' => 'Topic',
            'wrong_code' => 'Wrong class',
        ],
        'helpers' => [
            'line' => 'Line number in the wrong class.',
            'published' => 'Off = draft, students cannot see the exercise yet.',
        ],
        'plural' => 'Exercises',
        'singular' => 'Exercise',
        'warnings' => [
            'attempted' => [
                'description' => 'Students have already made this exercise. If you change the code or the errors, their earlier attempts may no longer match.',
                'heading' => 'This exercise has already been made',
            ],
        ],
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
