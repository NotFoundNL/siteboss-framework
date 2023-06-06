<?php

namespace NotFound\Framework\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cms_template')->insertOrIgnore([
            'id' => 1,
            'name' => 'Site',
            'filename' => 'Site',
            'enabled' => 1,
        ]);

        DB::table('cms_template')->insertOrIgnore([
            'id' => 2,
            'name' => 'Homepage',
            'filename' => 'Home',
            'enabled' => 1,

        ]);

        DB::table('menu')->insertOrIgnore([
            'id' => 1,
            'parent_id' => 0,
            'url' => 'root',
            'template_id' => 1,
            'language' => 1,
            'enabled' => 1,
            'attr' => 0,
            'properties' => '{}',
            'order' => 1,
            'status' => 'PUBLISHED',
            'menu' => 1,
        ]);

        DB::table('menu')->insertOrIgnore([
            'id' => 2,
            'parent_id' => 1,
            'url' => 'home',
            'template_id' => 2,
            'language' => 1,
            'enabled' => 1,
            'attr' => 0,
            'properties' => '{}',
            'order' => 2,
            'status' => 'PUBLISHED',
            'menu' => 1,
        ]);
    }
}
