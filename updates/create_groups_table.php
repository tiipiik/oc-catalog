<?php namespace Tiipiik\Catalog\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateGroupsTable extends Migration
{

    public function up()
    {
        // Add group_id to products
        Schema::table('tiipiik_catalog_products', function ($table) {
            $table->integer('group_id')->after('id')->nullable()->unsigned();
        });
        
        // Groups
        Schema::create('tiipiik_catalog_groups', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->default('');
            $table->timestamps();
        });
        
        // Relation between groups and custom fields
        Schema::create('tiipiik_catalog_group_field', function ($table) {
            $table->engine = 'InnoDB';
            $table->integer('custom_field_id')->unsigned();
            $table->integer('group_id')->unsigned();
            $table->primary(['custom_field_id', 'group_id']);
        });
        
    }

    public function down()
    {
        if (Schema::hasColumn('tiipiik_catalog_products', 'group_id')) {
            Schema::table('tiipiik_catalog_products', function ($table) {
                $table->dropColumn('group_id');
            });
        }
        Schema::dropIfExists('tiipiik_catalog_groups');
        Schema::dropIfExists('tiipiik_catalog_group_field');
    }
}
