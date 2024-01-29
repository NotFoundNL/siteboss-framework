<?php

namespace NotFound\Framework\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cms_user')->insertOrIgnore([
            'name' => 'Beheerder Notfound',
            'email' => env('SB_ADMIN_EMAIL', ''),
            'email_verified_at' => now(),
            'enabled' => 1,
        ]);

        DB::table('cms_usergroup')->insertOrIgnore([
            'user_id' => 1,
            'group_id' => 1,
        ]);
    }
}
