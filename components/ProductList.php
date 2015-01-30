<?php namespace Tiipiik\Catalog\Components;

use App;
use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Tiipiik\Catalog\Models\Category;
use Tiipiik\Catalog\Models\Product;

use October\Rain\Database\DataFeed;

class ProductList extends ComponentBase
{
    /*
     * The current category displayed
     * @var string
     */
    public $category;
    
    /*
     * A collection of products from the current category,
     * or the full products list
     * @array
     */
    public $products;

    public function componentDetails()
    {
        return [
            'name'        => 'tiipiik.catalog::lang.component.product_list.name',
            'description' => 'tiipiik.catalog::lang.component.product_list.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'categorySlug' => [
                'title'       => 'tiipiik.catalog::lang.component.product_list.param.category_param_title',
                'description' => 'tiipiik.catalog::lang.component.product_list.param.category_param_desc',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            /*
            'categoryParam' => [
                'title' => 'tiipiik.catalog::lang.component.product_list.param.category_param_title',
                'description' => 'tiipiik.catalog::lang.component.product_list.param.category_param_desc',
                'type' => 'string',
                'default' => ':slug'
            ],
            */
            /*
            'categoryFilter' => [
                'title' => 'Category filter',
                'description' => 'Select a category to filter the product list by. Leave empty to show all products.',
                'type' => 'string',
                'default' => ''
            ],
            */
            'productPage' => [
                'title'       => 'tiipiik.catalog::lang.component.product_list.param.product_page_title',
                'description' => 'tiipiik.catalog::lang.component.product_list.param.product_page_desc',
                'type'        => 'dropdown',
                'default'     => 'products/:slug'
            ],
            'productPageSlug' => [
                'title'       => 'tiipiik.catalog::lang.component.product_list.param.product_page_id_title',
                'description' => 'tiipiik.catalog::lang.component.product_list.param.product_page_id_desc',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            /*
            'productPageIdParam' => [
                'title'       => 'tiipiik.catalog::lang.component.product_list.param.product_page_id_title',
                'description' => 'tiipiik.catalog::lang.component.product_list.param.product_page_id_desc',
                'type'        => 'string',
                'default'     => ':slug',
            ],
            */
            'noProductsMessage' => [
                'title'        => 'tiipiik.catalog::lang.component.product_list.param.no_product_title',
                'description'  => 'tiipiik.catalog::lang.component.product_list.param.no_product_desc',
                'type'         => 'string',
                'default'      => 'No product found'
            ],
            'productsPerPage' => [
                'title'             => 'tiipiik.catalog::lang.component.product_list.param.products_per_page_title',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'tiipiik.catalog::lang.component.product_list.param.products_per_page_validation_message',
                'default'           => '10',
                'group'             => 'Pagination',
            ],
            'pageParam' => [
                'title'       => 'tiipiik.catalog::lang.component.product_list.param.page_param_title',
                'description' => 'tiipiik.catalog::lang.component.product_list.param.page_param_desc',
                'type'        => 'string',
                'default'     => ':page',
                'group'       => 'Pagination',
            ],
        ];
    }
    
    
    public function getProductPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }
    
    
    public function onRun()
    {
        // @deprecated remove if year >= 2015
        $deprecatedSlug = $this->propertyOrParam('productPageIdParam');
        
        // Use strict method only to avoid conflicts whith other plugins
        $this->productPage = $this->property('productPage');
        
        $category = $this->category = $this->loadCategory();
        
        if (!$category)
        {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }
        
        $currentPage = post('page');
        $products = $this->products = $this->listProducts();

        /*
         * Pagination
         */
        if ($products) {
            $queryArr = [];
            $queryArr['page'] = '';
            $paginationUrl = Request::url() . '?' . http_build_query($queryArr);

            if ($currentPage > ($lastPage = $products->getLastPage()) && $currentPage > 1)
                return Redirect::to($paginationUrl . $lastPage);

            $this->page['paginationUrl'] = $paginationUrl;
        }
        
        $this->noProductsMessage = $this->property('noProductsMessage');
        $this->productParam = $this->property('productParam');
        $this->productPageIdParam = $this->property('categorySlug', $deprecatedSlug);
        //$this->productPageIdParam = $this->property('categorySlug');
    }
    
    public function listProducts()
    {
        $categories = $this->category ? $this->category->id : null;
        
        $products = Product::with('categories')->listFrontEnd([
            'page' => $this->propertyOrParam('pageParam'),
            'perPage' => $this->propertyOrParam('productsPerPage'),
            'categories' => $categories,
        ]);
        
        return $products;
    }
    
    protected function loadCategory()
    {
        // @deprecated remove if year >= 2015
        $deprecatedCategorySlug = $this->propertyOrParam('categoryParam');
        
        $category = Category::make()->categoryDetails([
            'category' => $this->property('categorySlug', $deprecatedCategorySlug),
            //'category' => $this->property('categorySlug'),
        ]);
        
        if (empty($category))
            return null;
            
        $this->page->title = $category->name;
        
        return $category;
    }

}