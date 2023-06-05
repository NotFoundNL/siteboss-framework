<?php

//this file is published by the siteboss-framework package

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use NotFound\Framework\Database\Seeders\PackageDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PackageDatabaseSeeder::class);
    }
}
