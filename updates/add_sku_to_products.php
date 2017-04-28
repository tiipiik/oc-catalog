<?php
namespace Tiipiik\Catalog\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class AddSkuToProducts extends Migration
{

    public function up()
    {
        Schema::table('tiipiik_catalog_products', function ($table) {
            $table->string('sku')->after('is_published')->nullable();
        });
    }

    public function down()
    {
        if (Schema::hasColumn('tiipiik_catalog_products', 'sku')) {
            Schema::table('tiipiik_catalog_products', function ($table) {
                $table->dropColumn('sku');
            });
        }
    }
}
