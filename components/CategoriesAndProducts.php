<?php namespace Tiipiik\Catalog\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Tiipiik\Catalog\Models\Category;
use Tiipiik\Catalog\Models\Product;

class CategoriesAndProducts extends ComponentBase
{
    public $categories;
    public $noProductsMessage;
    public $categoryPage;
    public $productPage;

    public function componentDetails()
    {
        return [
            'name'        => 'Categories & Products',
            'description' => 'Display a list of categories and related products'
        ];
    }

    public function defineProperties()
    {
        return [
            'displayEmptyCategories' => [
                'title'       => 'Display empty category',
                'description' => 'Check to display empty categories',
                'type'        => 'checkbox',
                'default'     => 0
            ],
            'noProductsMessage' => [
                'title'        => 'No product message',
                'description'  => 'Message displayed if ther are no products inside a category',
                'type'         => 'string',
                'default'      => 'No product found in this category'
            ],
            'categoryPage' => [
                'title'       => 'tiipiik.catalog::lang.component.categories.param.category_page_title',
                'description' => 'tiipiik.catalog::lang.component.categories.param.category_page_desc',
                'type'        => 'dropdown',
                'default'     => 'category',
                'group'       => 'Links',
            ],
            'productPage' => [
                'title'       => 'Set page url for products',
                'description' => 'Set link for products pages',
                'type'        => 'dropdown',
                'default'     => 'product',
                'group'       => 'Links',
            ],
        ];
    }
    
    public function getCategoryPageOptions()
    {
        return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }
    
    public function getProductPageOptions()
    {
        return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->categories = $this->loadCategoriesWithProducts();
        $this->categoryPage = $this->property('categoryPage');
        $this->productPage = $this->property('productPage');
        $this->noProductsMessage = $this->property('noProductsMessage');
    }

    protected function loadCategoriesWithProducts()
    {
        $categories = Category::with('products')->orderBy('name');
        if ($this->property('displayEmptyCategories') == 0) {
            $categories->whereHas('products', function ($query) {
                $query->whereIsPublished(1);
            });
        }
        $categories = $categories->get();

        if (!$categories) {
            return null;
        }
        
        /*
         * Add a "url" helper attribute for linking to each category
         */
        $categories->each(function ($category) {
            $category->setUrl($this->property('categoryPage'), $this->controller);
            if (isset($category->products)) {
                // Display only published products
                $products_filtered = $category->products->filter(function ($value, $key) {
                    return $value->is_published > 0;
                });
                $category->products = $products_filtered->all();
                // Add url to products only after filter or it will not work
                $category->products->each(function ($product) {
                    $product->setUrl($this->property('productPage'), $this->controller);
                });
            }
        });

        return $categories;
    }
}
