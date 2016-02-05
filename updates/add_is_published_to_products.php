<?php namespace Tiipiik\Catalog\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddIsPublishedToProducts extends Migration
{

    public function up()
    {
        Schema::table('tiipiik_catalog_products', function ($table) {
            $table->boolean('is_published')->after('id')->default(0);
        });
    }

    public function down()
    {
        if (Schema::hasColumn('tiipiik_catalog_products', 'is_published')) {
            Schema::table('tiipiik_catalog_products', function ($table) {
                $table->dropColumn('is_published');
            });
        }
    }
}
