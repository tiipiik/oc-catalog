<?php namespace TiipiiK\Catalog\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class FixBrandPublished extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('tiipiik_catalog_brands', 'published')) {
            Schema::table('tiipiik_catalog_brands', function ($table) {
                $table->dropColumn('published');
            });
        }
        
        Schema::table('tiipiik_catalog_brands', function ($table) {
            $table->boolean('published')->after('description')->nullable()->default(0);
        });
    }

    public function down()
    {
        //
    }
}
