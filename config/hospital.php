<?php

return [
    'name' => env('HOSPITAL_NAME', 'Amani Hospital'),
    'tagline' => 'Compassionate care, simplified',
    'description' => 'Register online for a consultation at Amani Hospital. Choose a department, pick a time and we will match you with the right doctor.',

    // Contact details shown in the footer and in structured data.
    'phone' => env('HOSPITAL_PHONE', '+255 700 000 000'),
    'email' => env('HOSPITAL_EMAIL', 'care@amanihospital.test'),
    'address' => env('HOSPITAL_ADDRESS', ''),

    // Default country calling code used to normalise phone numbers (Tanzania).
    'country_code' => env('HOSPITAL_COUNTRY_CODE', '255'),
];
