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
    'exercise' => [
        'actions' => [
            'close' => 'Sluiten',
            'preview' => 'Voorbeeld',
        ],
        'fields' => [
            'attempts_count' => 'Pogingen',
            'correct_code' => 'Goede class',
            'error_title' => 'Titel van de fout',
            'errors' => 'Fouten',
            'explanation' => 'Uitleg',
            'hint' => 'Hint',
            'line' => 'Regel',
            'published' => 'Gepubliceerd',
            'stars' => 'Sterren',
            'title' => 'Titel',
            'topic' => 'Onderwerp',
            'wrong_code' => 'Foute class',
        ],
        'helpers' => [
            'line' => 'Regelnummer in de foute class.',
            'published' => 'Uit = concept, studenten zien de oefening dan nog niet.',
        ],
        'plural' => 'Oefeningen',
        'singular' => 'Oefening',
        'warnings' => [
            'attempted' => [
                'description' => 'Studenten hebben deze oefening al gemaakt. Als je de code of de fouten wijzigt, kloppen hun eerdere pogingen mogelijk niet meer.',
                'heading' => 'Deze oefening is al gemaakt',
            ],
        ],
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
