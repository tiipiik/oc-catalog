<?php namespace Tiipiik\Catalog\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddGroupToStores extends Migration
{

    public function up()
    {
        Schema::table('tiipiik_catalog_stores', function($table)
        {
            $table->integer('group_id')->after('id')->default(0);
        });
        
        Schema::table('tiipiik_catalog_custom_values', function($table)
        {
            $table->integer('store_id')->unsigned()->nullable()->index()->after('product_id');
        });
    }

    public function down()
    {
        if (Schema::hasColumn('tiipiik_catalog_stores', 'group_id'))
        {
            Schema::table('tiipiik_catalog_stores', function($table)
            {
                $table->dropColumn('group_id');
            });
        }
        
        if (Schema::hasColumn('tiipiik_catalog_custom_values', 'store_id'))
        {
            Schema::table('tiipiik_catalog_custom_values', function($table)
            {
                $table->dropColumn('store_id');
            });
        }
    }

}
