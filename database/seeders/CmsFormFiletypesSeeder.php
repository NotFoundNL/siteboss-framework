<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsFormFiletypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cms_form_filetypes')->insertOrIgnore(
            [
                [
                    'name' => 'pdf',
                    'display_name' => 'PDF',
                ],
                [
                    'name' => 'documents',
                    'display_name' => 'Documenten',
                ],
            ]
        );
    }
}
