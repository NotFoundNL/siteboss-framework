<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cms_menu')->insertOrIgnore([
            'level' => '0',
            'title' => 'Home',
            'enabled' => 1,
            'to' => '/home/',
            'icon' => 'home',
        ]);
        DB::table('cms_menu')->insertOrIgnore([
            'level' => '0',
            'rights' => 'user-management',
            'title' => 'Users',
            'enabled' => 1,
            'to' => '/app/users/',
            'icon' => 'users',
        ]);
        DB::table('cms_menu')->insertOrIgnore([
            'level' => '0',
            'rights' => 'admin',
            'title' => 'CMS Editor',
            'enabled' => 1,
            'to' => '/app/editor/',
            'icon' => 'cogs',
        ]);
        DB::table('cms_menu')->insertOrIgnore([
            'level' => '0',
            'rights' => '',
            'title' => 'Pagina\'s',
            'enabled' => 1,
            'to' => '/app/menu/1',
            'icon' => 'list',
        ]);
    }
}
