<?php

use App\Models\Forms\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCmsFormTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('cms_form', function (Blueprint $table) {
            $table->id();
            $table->set('type', ['combination', 'form'])->default('form');
            $table->string('name', 90)->unique('name');
            $table->foreignIdFor(Category::class, 'category_id')->nullable();
            $table->text('success_text')->nullable();
            $table->string('success_action', 128)->nullable();
            $table->string('notification_address', 128)->nullable();
            $table->text('confirmation_mail')->nullable();
            $table->tinyInteger('endpoint')->default(1);
            $table->tinyInteger('archived')->default(0);
            $table->set('status', ['DRAFT', 'PUBLISHED', 'DELETED'])->default('PUBLISHED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cms_form');
    }
}
