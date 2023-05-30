<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cms_group')->insertOrIgnore([
            [
                'parent' => 0,
                'internal' => 'admin',
                'name' => 'Beheerder',
            ],
            [
                'parent' => 1,
                'internal' => 'forms',
                'name' => 'Formbuilder',
            ],
            [
                'parent' => 1,
                'internal' => 'forms-formsettings',
                'name' => 'Formbuilder texts',
            ],
            [
                'parent' => 1,
                'internal' => 'forms-data',
                'name' => 'Formbuilder dataviewer',
            ],
            [
                'parent' => 1,
                'internal' => 'user-management',
                'name' => 'User management',
            ],
        ]);
    }
}
