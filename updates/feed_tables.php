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
        Category::create(['name' => 'Motorbike', 'slug' => 'motorbike']);
        Category::create(['name' => 'Kawasaki', 'slug' => 'kawasaki']);
        
        // Create custom fields first, as they are automatically added to product after create
        CustomField::create([
            'template_code' => 'color',
            'display_name' => 'Color',
            'default_value' => 'Green'
        ]);
        CustomField::create([
            'template_code' => 'options',
            'display_name' => 'Options'
        ]);
        
        // Create product. custom fields are added after creation
        Product::create([
            'category_id' => 2,
            'title' => 'Kawasaki 1400 ZZR',
            'slug' => 'kawasaki-1400-zzr',
            'price' => '10000',
        ]);
        
        // Link product to categories
        DB::insert('insert into tiipiik_catalog_prods_cats (category_id, product_id) values (?, ?)', ['2', '1']);
        
    }

}