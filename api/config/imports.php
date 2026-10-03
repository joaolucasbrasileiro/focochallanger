<?php

return [
    'files' => [
        'hotels' => env('IMPORT_HOTELS_PATH', base_path('../hotels.xml')),
        'rooms' => env('IMPORT_ROOMS_PATH', base_path('../rooms.xml')),
        'reservations' => env('IMPORT_RESERVATIONS_PATH', base_path('../reserves.xml')),
    ],
];
