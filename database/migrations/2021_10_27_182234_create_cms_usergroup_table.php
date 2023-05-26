<?php

use App\Models\CmsGroup;
use App\Models\CmsUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsUsergroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_usergroup', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CmsUser::class, 'user_id')->nullable();
            $table->foreignIdFor(CmsGroup::class, 'group_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cms_usergroup');
    }
}
