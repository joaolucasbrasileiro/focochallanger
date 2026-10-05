<?php

return [
    'files' => [
        'hotels' => env('IMPORT_HOTELS_PATH', base_path('../imports/incoming/hotels.xml')),
        'rooms' => env('IMPORT_ROOMS_PATH', base_path('../imports/incoming/rooms.xml')),
        'reservations' => env('IMPORT_RESERVATIONS_PATH', base_path('../imports/incoming/reserves.xml')),
    ],
    'archive_path' => env('IMPORT_ARCHIVE_PATH', base_path('../imports/archive')),
];
