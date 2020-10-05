<?php
namespace Tiipiik\Catalog\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreatePropertiesTable extends Migration
{
    public function up()
    {
        Schema::create('tiipiik_catalog_properties', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('type')->default(1);
            $table->text('description')->nullable();
            $table->mediumText('values_array')->nullable();
            $table->boolean('is_used')->default(false);
            $table->timestamps();
        });

        Schema::create('tiipiik_catalog_prods_props', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('product_id')->unsigned();
            $table->integer('property_id')->unsigned();
            $table->string('value')->nullable();
            $table->primary(['product_id', 'property_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tiipiik_catalog_properties');
        Schema::dropIfExists('tiipiik_catalog_prods_props');
    }
}
