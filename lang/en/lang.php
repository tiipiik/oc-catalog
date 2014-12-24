<?php

return [
    'plugin_name' => 'Catalog',
    'products'  =>  [
        'new_product' => 'New Product',
        'menu_label' => 'Products',
        'id' => 'ID',
        'title' => 'Title',
        'title_ph' => 'Title',
        'slug' => 'Slug',
        'slug_ph' => 'Slug',
        'details_tab_title' => 'Details',
        'items_available' => 'Items available',
        'items_available_ph' => 'Items available',
        'description' => 'Description',
        'description_ph' => 'Description',
        'price' => 'Price',
        'price_ph' => 'Price',
        'discount_price' => 'Discount price',
        'discount_price_ph' => 'Discount price',
        'featured_images' => 'Featured images',
        'category' => 'Category',
        'category_tab_title' => 'Categories',
        'categories_cmt' => 'Select category the product belongs to',
        'return_to_list' => 'Return to product list',
    ],
    'categories'  =>  [
        'new_category' => 'New Category',
        'reorder_category' => 'Manage Categories Order',
        'menu_label' => 'Categories',
        'return_to_list' => 'Return to categories',
    ],
    'custom_fields'  =>  [
        'new_category' => 'New Custom Field',
        'menu_label' => 'Custom Fields',
    ],
    'config' => [
        'form' => [
            'name' => 'Product',
            'create_title' => 'Create Product',
            'update_title' => 'Edit Product',
            'category_name' => 'Product',
            'category_create_title' => 'Create Category',
            'category_update_title' => 'Edit Category',
            'return_to_list' => 'Return to categories list',
        ],
        'list' => [
            'title' => 'Manage Products',
            'category_title' => 'Manage Categories',
        ],
    ],
    'settings' => [
        'access_categories' => 'Manage the Catalog categories',
        'access_products' => 'Manage the Catalog products',
        'access_custom_fields' => 'Manage the Catalog product\'s custom fields',
    ],
    'catalog' => [
        'delete_confirm' => 'Do you really want to delete this product ?',
    ],
];