<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsFormCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cms_form_categories')->insertOrIgnore(
            [
                [
                    'name' => 'Formulieren',
                    'slug' => 'forms',
                ],
            ]
        );
    }
}
