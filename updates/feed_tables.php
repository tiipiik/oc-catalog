<?php namespace Tiipiik\Catalog;

use DB;
use October\Rain\Database\Updates\Seeder;
use Tiipiik\Catalog\Models\Category;
use Tiipiik\Catalog\Models\Product;
use Tiipiik\Catalog\Models\CustomField;
use Tiipiik\Catalog\Models\CustomValue;

class SeedTables extends Seeder
{

    public function run()
    {
        Category::create(['name' => 'Toys', 'slug' => 'toys']);
        Category::create(['name' => 'Motorbike', 'slug' => 'motorbike']);
        
        Product::create([
            'category_id' => 2,
            'title' => 'Kawasaki 1400 ZZR',
            'slug' => 'kawasaki-1400-zzr',
            'price' => '10000',
        ]);
        
        CustomField::create([
            'template_code' => 'color',
            'display_name' => 'Color'
        ]);
        CustomField::create([
            'template_code' => 'options',
            'display_name' => 'Options'
        ]);
        
        // Insert custom field values
        CustomValue::create([
            'product_id' => '1',
            'custom_field_id' => '1',
            'value' => 'Green, Black'
        ]);
        
        // Product custom fields and values
        DB::insert('insert into tiipiik_catalog_csf_csv (custom_value_id, custom_field_id) values (1, 1)');
        
        // Product categories
        DB::insert('insert into tiipiik_catalog_prods_cats (category_id, product_id) values (?, ?)', ['2', '1']);

    }

}