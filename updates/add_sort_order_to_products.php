<?php namespace Tiipiik\Catalog\Updates;

use Schema;
use Tiipiik\Catalog\Models\Product;
use October\Rain\Database\Updates\Migration;

class AddSortOrderToProducts extends Migration
{

    public function up()
    {
        Schema::table('tiipiik_catalog_products', function ($table) {
            $table->boolean('sort_order')->after('discount_price')->default(0);
        });
        // Create default order to make sortable wroks
        $products = Product::all();
        if (isset($products) && sizeof($products) != 0) {
            foreach ($products as $product) {
                $product_update = Product::find($product->id);
                $product_update->sort_order = $product->id;
                $product_update->save();
            }
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tiipiik_catalog_products', 'sort_order')) {
            Schema::table('tiipiik_catalog_products', function ($table) {
                $table->dropColumn('sort_order');
            });
        }
    }
}
