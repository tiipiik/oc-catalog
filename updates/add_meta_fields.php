<?php namespace TiipiiK\Catalog\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddMetaFields extends Migration
{
    public function up()
    {
        Schema::table('tiipiik_catalog_products', function ($table) {
            $table->text('meta_title')->after('brand_id')->nullable();
            $table->text('meta_desc')->after('meta_title')->nullable();
        });

        Schema::table('tiipiik_catalog_brands', function ($table) {
            $table->text('meta_title')->after('description')->nullable();
            $table->text('meta_desc')->after('meta_title')->nullable();
        });

        Schema::table('tiipiik_catalog_stores', function ($table) {
            $table->text('meta_title')->after('description')->nullable();
            $table->text('meta_desc')->after('meta_title')->nullable();
        });
    }

    public function down()
    {
        if (Schema::hasColumn('tiipiik_catalog_products', 'meta_title')) {
            Schema::table('tiipiik_catalog_products', function ($table) {
                $table->dropColumn('meta_title');
            });
        }
        if (Schema::hasColumn('tiipiik_catalog_products', 'meta_desc')) {
            Schema::table('tiipiik_catalog_products', function ($table) {
                $table->dropColumn('meta_desc');
            });
        }

        if (Schema::hasColumn('tiipiik_catalog_brands', 'meta_title')) {
            Schema::table('tiipiik_catalog_brands', function ($table) {
                $table->dropColumn('meta_title');
            });
        }
        if (Schema::hasColumn('tiipiik_catalog_brands', 'meta_desc')) {
            Schema::table('tiipiik_catalog_brands', function ($table) {
                $table->dropColumn('meta_desc');
            });
        }

        if (Schema::hasColumn('tiipiik_catalog_stores', 'meta_title')) {
            Schema::table('tiipiik_catalog_stores', function ($table) {
                $table->dropColumn('meta_title');
            });
        }
        if (Schema::hasColumn('tiipiik_catalog_stores', 'meta_desc')) {
            Schema::table('tiipiik_catalog_stores', function ($table) {
                $table->dropColumn('meta_desc');
            });
        }
    }
}
