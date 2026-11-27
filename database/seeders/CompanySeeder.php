<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $companies = [
            [
                'name' => 'Nestle Pakistan Ltd',
                'branch' => 'Lahore',
                'address' => 'Packages Mall, Shahrah-e-Roomi, PO Amer Sidhu Lahore- 54760, Pakistan',
                'ntn' => '0225862-5',
                'strn' => '03-08-2009-001-46',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Nestle Pakistan Ltd',
                'branch' => 'Sheikhupura',
                'address' => '29-km Lahore - Sheikhupura Road, Sheikhupura, Pakistan. Phone: (056) 3406615-29',
                'ntn' => '0225862-5',
                'strn' => '03-08-2009-001-46',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Nestle Pakistan Ltd',
                'branch' => 'Kabirwala',
                'address' => '10-km, Khanewal Road, Kabirwala, District Khanewal, Pakistan. Phone: (021) 38402086, Fax: (065) 2411432',
                'ntn' => '0225862-5',
                'strn' => '03-08-2009-001-46',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Nestle Pakistan Ltd',
                'branch' => 'Islamabad',
                'address' => 'Plot No. 32, Street No. 3, Sector I-10/3, Industrial Area Islamabad, Pakistan. Phone: (051) 4445991-3',
                'ntn' => '0225862-5',
                'strn' => '03-08-2009-001-46',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Nestle Pakistan Ltd',
                'branch' => 'Karachi',
                'address' => 'Plot No. A23, North Western Industrial Area, Port Qasim Karachi, Pakistan. Phone: (021) 34720152-4',
                'ntn' => '0225862-5',
                'strn' => '03-08-2009-001-46',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('companies')->insert($companies);
    }
}
