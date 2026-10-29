<?php

declare(strict_types=1);

return [
    'fields' => [
        'created_at' => 'Aangemaakt op',
        'deleted_at' => 'Verwijderd op',
        'updated_at' => 'Bijgewerkt op',
    ],
    'user' => [
        'fields' => [
            'email' => 'E-mail',
            'name' => 'Naam',
            'password' => 'Wachtwoord',
            'role' => 'Rol',
        ],
        'helpers' => [
            'password' => 'Laat leeg om het huidige wachtwoord te behouden.',
        ],
        'plural' => 'Gebruikers',
        'singular' => 'Gebruiker',
    ],
];
