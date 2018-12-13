<?php
namespace Tiipiik\Catalog\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class FixSortOrderOnProducts extends Migration
{

    public function up()
    {
        Schema::table('tiipiik_catalog_products', function ($table) {
            $table->integer('sort_order')->unsigned()->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('tiipiik_catalog_products', function ($table) {
            $table->dropColumn('sort_order');
        });
        Schema::table('tiipiik_catalog_products', function ($table) {
            $table->boolean('sort_order')->after('discount_price')->default(0);
        });
    }
}
