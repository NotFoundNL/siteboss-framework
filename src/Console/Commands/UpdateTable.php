<?php

namespace NotFound\Framework\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Generates a migration that adds timestamps and soft deletes to a table.
 *
 * When the table still has a legacy `status` column, the generated migration
 * migrates rows with a DRAFT or DELETED status to `deleted_at` (soft deleted)
 * and drops the `status` column afterwards.
 */
class UpdateTable extends Command
{
    /**
     * @var string
     */
    protected $signature = 'siteboss:update-table
        {table* : One or more table names to convert}
        {--force : Overwrite the migration if it already exists}';

    /**
     * @var string
     */
    protected $description = 'Generate a migration adding timestamps and soft deletes to a table, migrating DRAFT/DELETED status values to deleted_at';

    public function __construct(private Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        foreach ($this->argument('table') as $table) {
            if (! Schema::hasTable($table)) {
                $this->error("Table [{$table}] does not exist.");

                return self::FAILURE;
            }
        }

        $tables = $this->argument('table');
        $slug = count($tables) === 1
            ? 'add_soft_deletes_to_'.$tables[0].'_table'
            : 'add_soft_deletes_to_tables';

        $path = database_path('migrations/'.date('Y_m_d_His').'_'.$slug.'.php');

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->error("Migration [{$path}] already exists.");

            return self::FAILURE;
        }

        $this->files->put($path, $this->buildMigration($tables));

        $this->info('Created migration: '.Str::after($path, database_path().'/'));
        $this->line('Review it, then run <comment>php artisan migrate</comment>.');

        return self::SUCCESS;
    }

    /**
     * @param  array<int, string>  $tables
     */
    private function buildMigration(array $tables): string
    {
        $tableList = collect($tables)
            ->map(fn (string $table) => "        '".addslashes($table)."',")
            ->implode("\n");

        return <<<PHP
        <?php

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration
        {
            /**
             * Tables to add timestamps and soft deletes to.
             *
             * @var array<int, string>
             */
            private array \$tables = [
        {$tableList}
            ];

            /**
             * Statuses that should be treated as soft deleted.
             *
             * @var array<int, string>
             */
            private array \$deletedStatuses = ['DRAFT', 'DELETED'];

            public function up(): void
            {
                foreach (\$this->tables as \$tableName) {
                    if (! Schema::hasColumn(\$tableName, 'created_at') && ! Schema::hasColumn(\$tableName, 'updated_at')) {
                        Schema::table(\$tableName, function (Blueprint \$table) {
                            \$table->timestamps();
                        });
                    }

                    if (! Schema::hasColumn(\$tableName, 'deleted_at')) {
                        Schema::table(\$tableName, function (Blueprint \$table) {
                            \$table->softDeletes();
                        });
                    }

                    if (Schema::hasColumn(\$tableName, 'status')) {
                        DB::table(\$tableName)
                            ->whereIn(DB::raw('UPPER(status)'), \$this->deletedStatuses)
                            ->whereNull('deleted_at')
                            ->update(['deleted_at' => now()]);

                        Schema::table(\$tableName, function (Blueprint \$table) {
                            \$table->dropColumn('status');
                        });
                    }
                }
            }

            public function down(): void
            {
                foreach (\$this->tables as \$tableName) {
                    if (! Schema::hasColumn(\$tableName, 'status')) {
                        Schema::table(\$tableName, function (Blueprint \$table) {
                            \$table->enum('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('PUBLISHED');
                        });

                        DB::table(\$tableName)
                            ->whereNotNull('deleted_at')
                            ->update(['status' => 'DELETED']);
                    }

                    if (Schema::hasColumn(\$tableName, 'deleted_at')) {
                        Schema::table(\$tableName, function (Blueprint \$table) {
                            \$table->dropSoftDeletes();
                        });
                    }
                }
            }
        };

        PHP;
    }
}
