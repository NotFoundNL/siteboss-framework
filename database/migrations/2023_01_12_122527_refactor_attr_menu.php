<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NotFound\Framework\Models\Menu;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('menu', 'menu')) {
            Schema::table('menu', function (Blueprint $table) {
                $table->boolean('menu')->default(0);
            });
        }
        if (! Schema::hasColumn('menu', 'deleted_at')) {
            Schema::table('menu', function (Blueprint $table) {
                $table->softDeletes();
                $table->timestamps();
            });
        }
        foreach (Menu::get() as $menuItem) {
            $this->legacyConveryBitToJSon($menuItem);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('menu', function (Blueprint $table) {
            $table->renameColumn('menu', 'isMenu');
        });
    }

    private function legacyConveryBitToJSon($menuitem)
    {
        $ATTR_FOLDER = 1;  // folder
        $ATTR_NOMOVE = 2; // dont allow to sort
        $ATTR_NOEDIT = 4; // cannot edit the menu
        $ATTR_NODELETE = 8; // cannot delete
        $ATTR_NOMENU = 128; // cannot set the menu to true
        $ATTR_MENU = 256; // cannot set the menu to true
        $ATTR_NOMOVECHILD = 512;
        $ATTR_NOENABLECHILD = 1024;
        $ATTR_NOnewCHILD = 4096;
        $ATTR_CHANGEPARENT = 8192;
        $ATTR_NODELETECHILD = 16384;
        $ATTR_NOENABLE = 32768;

        $properties = $menuitem->properties;
        $excludeFromSearch = false;
        if (isset($properties->excludeFromSearch)) {
            $excludeFromSearch = $properties->excludeFromSearch;
        }
        $isfolder = 0;

        if (isset($menuitem->template)) {
            if ($menuitem->template()?->pluck('attr')) {
                $isfolder = $menuitem->template()?->pluck('attr')->get(0);
            }
        }
        $newProperties = (object) [
            'isFolder' => (bool) (intval($isfolder) & $ATTR_FOLDER),
            'allowDelete' => ! (intval($menuitem->attr) & $ATTR_NODELETE),
            'allowSort' => ! (intval($menuitem->attr) & $ATTR_NOMOVE),
            'allowEdit' => ! (intval($menuitem->attr) & $ATTR_NOEDIT),
            'allowMenu' => ! (intval($menuitem->attr) & $ATTR_NOMENU),
            'allowSortChildren' => ! (intval($menuitem->attr) & $ATTR_NOMOVECHILD),
            'allowEnableChildren' => ! (intval($menuitem->attr) & $ATTR_NOENABLECHILD),
            'allowAddChildren' => ! (intval($menuitem->attr) & $ATTR_NOnewCHILD),
            'allowChangeParent' => (bool) (intval($menuitem->attr) & $ATTR_CHANGEPARENT),
            'allowDeleteChildren' => ! (intval($menuitem->attr) & $ATTR_NODELETECHILD),
            'allowEnabled' => ! (intval($menuitem->attr) & $ATTR_NOENABLE),
            'excludeFromSearch' => (bool) $excludeFromSearch,
        ];
        $menuitem->properties = ($newProperties);
        $menuitem->menu = (bool) (intval($menuitem->attr) & $ATTR_NODELETECHILD);
        $menuitem->save();
    }
};
