<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use NotFound\Framework\Models\CmsMenu;
use NotFound\Framework\Models\Menu;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Models\TableItem;
use NotFound\Framework\Models\Template;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('menu', function (Blueprint $table) {
            $table->dropColumn('updated');
        });
        Schema::table('menu', function (Blueprint $table) {
            $table->timestamp('updated');
        });
        $this->addTimestampsToTables();
        $this->transitionStatusColumnToDeletedAt();

        $this->updateItemNames();

        Schema::table('cms_user', function (Blueprint $table) {
            $table->renameColumn('login', 'email');
            $table->string('sub');
            $table->removeColumn('password');
            $table->removeColumn('session_id');
        });

        $this->moveParamsFromMenuToTemplate();

        $this->removeDatabasePrefixFromTable();

        Schema::table('cms_table', function (Blueprint $table) {
            $table->boolean('allow_sort')->after('name');
            $table->boolean('allow_delete')->after('name');
            $table->boolean('allow_create')->after('name');
        });

        Schema::table('cms_menu', function (Blueprint $table) {
            $table->string('to');
        });

        Schema::table('cms_search', function (Blueprint $table) {
            $table->string('url', 256)->change();
        });

        $this->convertMenuItems();

        $this->createImageColumns();

        Schema::table('cms_tableitem', function (Blueprint $table) {
            $table->json('server_properties')->nullable();
        });
        Schema::table('cms_templateitem', function (Blueprint $table) {
            $table->json('server_properties')->nullable();
        });
    }

    private function createImageColumns()
    {
        TableItem::where('type', 'image')->get()->each(function ($tableItem) {
            Schema::table($tableItem->table->table, function (Blueprint $table) use ($tableItem) {
                $table->string($tableItem->internal);
            });
        });
    }

    private function updateItemNames()
    {
        foreach (TableItem::all() as $item) {
            $item->type = $this->convertType($item->type);
            $item->save();
        }
    }

    private function convertType($type)
    {
        $type = strtolower($type);
        switch ($type) {
            case 'datepicker':
                $type = 'DatePicker';
                break;
            case 'timepicker':
                $type = 'TimePicker';
                break;
            case 'tableselect':
                $type = 'TableSelect';
                break;

            default:
                $type = ucfirst($type);
        }

        return $type;
    }

    private function convertMenuItems()
    {
        foreach (CmsMenu::all() as $menu) {
            if ($menu->target === 'users.php') {
                $menu->to = '/app/users/';
            } elseif ($menu->target === 'home.php') {
                $menu->to = '/home/';
            } elseif ($menu->target === 'users.php') {
                $menu->to = '/app/users/';
            } elseif (preg_match('/^table-([a-z0-9]+)\.cms$/', $menu->target, $matches)) {
                $menu->to = sprintf('/table/%s/', $matches[1]);
            } elseif (preg_match('/^custom-([a-z0-9]+)\.cms$/', $menu->target, $matches)) {
                $menu->to = sprintf('/app/site/%s/', $matches[1]);
            } else {
                $menu->to = $menu->url ?? '/fix/ '.$menu->target;
            }
            $menu->save();
        }
    }

    private function moveParamsFromMenuToTemplate()
    {
        Schema::table('cms_template', function (Blueprint $table) {
            $table->string('params', 128);
        });

        foreach (Menu::all() as $menu) {
            Template::whereId($menu->template)->update(['params' => $menu->params]);
        }

        Schema::table('menu', function (Blueprint $table) {
            $table->dropColumn('params');
        });
    }

    private function removeDatabasePrefixFromTable()
    {
        foreach (Table::all() as $table) {
            $table->table = str_replace('[][]', '', $table->table);
            $table->table = str_replace(env('DB_PREFIX'), '', $table->table);
            $table->save();
        }
    }

    private function addTimestampsToTables()
    {
        $tables = ['cms_menu', 'cms_config', 'cms_form', 'cms_form_data', 'cms_form_fields', 'cms_search', 'cms_table', 'cms_tableitem', 'cms_template', 'cms_templateitem'];
        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    private function transitionStatusColumnToDeletedAt()
    {
        $tables = ['cms_content_blocks', 'cms_editorsettings', 'cms_group', 'cms_menu', 'cms_table', 'cms_tableitem', 'cms_template',
            'cms_templateitem', 'cms_form', 'cms_form_categories', 'cms_form_data', 'cms_form_fields', 'cms_form_filetypes', 'cms_form_properties', ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->softDeletes();
            });

            DB::table($tableName)->where('status', 'deleted')->update(['deleted_at' => now()]);

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
