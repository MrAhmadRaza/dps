<?php

/*
|--------------------------------------------------------------------------
| School profile (public website)
|--------------------------------------------------------------------------
| Every fact shown on the public landing page lives here, so it can be
| corrected in one place. Items marked "VERIFY" are placeholders carried
| over from the previous site and should be confirmed by the school.
*/

return [
    'name'       => 'Dunyapur Public School',
    'short_name' => 'DPS Dunyapur',
    'motto'      => ['Knowledge', 'Discipline', 'Success'],
    'founded'    => 1992,                                   // VERIFY (old site said both 1985 and 1992)

    'address'    => 'Multan Road, Dunyapur, District Lodhran, Punjab, Pakistan', // VERIFY
    'phone'      => '0608-304869',                      // VERIFY (placeholder)
    'whatsapp'   => '+92000000000',                         // digits only, used for wa.me link
    'email'      => 'dpsdunyapur@gmail.com',              // VERIFY (placeholder)
    'hours'      => 'Mon – Sat, 8:00 AM – 2:00 PM',         // VERIFY
    'map_query'  => 'Dunyapur Public School, Dunyapur',

    'principal'  => [
        'name'  => 'Mr. Muhammad Amjad',
        'title' => 'Principal, Dunyapur Public School',
    ],

    // Headline numbers shown under the hero ("years of legacy" is computed
    // from 'founded'). VERIFY the student count (carried over from old site).
    'stats' => [
        ['value' => 2300, 'suffix' => '+', 'label' => 'Students enrolled'],
        ['value' => 4,    'suffix' => '',  'label' => 'Dedicated science labs'],
    ],

    'admissions_session' => '2026–27',

    'social' => [
        'facebook'  => 'https://www.facebook.com/dps-official',          // VERIFY
        'instagram' => 'https://www.instagram.com/dps-official',         // VERIFY
        'youtube'   => 'https://www.youtube.com/dps-official',           // VERIFY
    ],
];
