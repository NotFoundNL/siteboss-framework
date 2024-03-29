<?php

namespace NotFound\Framework\Database\Seeders;

use Illuminate\Database\Seeder;

class PackageDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->callFilesFromThisDirectory(__DIR__, 'NotFound\\Framework\\Database\\Seeders\\');
    }

    private function callFilesFromThisDirectory(string $directory, string $namespace): void
    {
        $seeders = scandir($directory);
        foreach ($seeders as $file) {
            if (in_array($file, ['.', '..', 'PackageDatabaseSeeder.php']) || substr($file, -4) !== '.php') {
                continue;
            }

            $fileNameWithoutExtension = explode('.', $file)[0];
            preg_match('/^[0-9]+_(.+)/i', $fileNameWithoutExtension, $matches);
            if (isset($matches[1])) {
                require_once $directory.'/'.$file;
                $fileNameWithoutExtension = $matches[1];
            }

            $this->call($namespace.$fileNameWithoutExtension);
        }
    }
}
