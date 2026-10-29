<?php

declare(strict_types=1);

return [
    'attempt' => [
        'fields' => [
            'exercise' => 'Oefening',
            'found' => 'Gevonden',
            'gave_up' => 'Opgegeven',
            'number' => 'Poging',
            'selected_lines' => 'Aangeklikte regels',
            'stars' => 'Sterren',
            'wrong' => 'Fout',
        ],
        'plural' => 'Pogingen',
        'singular' => 'Poging',
    ],
    'fields' => [
        'created_at' => 'Aangemaakt op',
        'deleted_at' => 'Verwijderd op',
        'updated_at' => 'Bijgewerkt op',
    ],
    'student' => [
        'fields' => [
            'badges' => 'Badges',
            'badges_count' => 'Badges',
            'email' => 'E-mail',
            'exercises_count' => 'Gemaakte oefeningen',
            'name' => 'Naam',
            'stars' => 'Sterren',
        ],
        'placeholders' => [
            'badges' => 'Nog geen badges',
        ],
        'plural' => 'Studenten',
        'singular' => 'Student',
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
