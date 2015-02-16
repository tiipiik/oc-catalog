<?php namespace Tiipiik\Catalog\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePivotGroupFieldTable extends Migration
{

    public function up()
    {
        Schema::create('tiipiik_catalog_group_field', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('group_id')->unsigned();
            $table->integer('custom_field_id')->unsigned();
            $table->primary('group_id', 'custom_field_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tiipiik_catalog_group_field');
    }

}
