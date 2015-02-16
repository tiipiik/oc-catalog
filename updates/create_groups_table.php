<?php namespace Tiipiik\Catalog\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateGroupsTable extends Migration
{

    public function up()
    {
        Schema::create('tiipiik_catalog_groups', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->default('');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tiipiik_catalog_groups');
    }

}
