<?php

namespace NotFound\Framework\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LangTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        DB::table('lang')->insertOrIgnore([
            'language' => 'Nederlands',
            'url' => 'nl',
            'enabled' => 1,
            'flag' => 'nl',
            'order' => 1,
            'default' => 1,
        ]);
    }
}
