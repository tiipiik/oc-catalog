<?php namespace Tiipiik\Catalog\Components;

use App;
use Request;
use Redirect;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Tiipiik\Catalog\Models\Category;
use Tiipiik\Catalog\Models\Product;

//use October\Rain\Database\DataFeed;

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
            'useCategoryFilter' => [
                'title'       => 'tiipiik.catalog::lang.component.product_list.param.usecategoryfilter_param_title',
                'description' => 'tiipiik.catalog::lang.component.product_list.param.usecategoryfilter_param_desc',
                'type'        => 'checkbox',
                'default'     => 0,
                'group'       => 'Filter',
            ],
            'categoryFilter' => [
                'title'       => 'tiipiik.catalog::lang.component.product_list.param.categoryfilter_param_title',
                'description' => 'tiipiik.catalog::lang.component.product_list.param.categoryfilter_param_desc',
                'type'        => 'string',
                'default'     => '',
                'group'       => 'Filter',
            ],
            'productPage' => [
                'title'       => 'tiipiik.catalog::lang.component.product_list.param.product_page_title',
                'description' => 'tiipiik.catalog::lang.component.product_list.param.product_page_desc',
                'type'        => 'dropdown',
                'default'     => 'products/:slug',
                'group'       => 'Products',
            ],
            'productPageSlug' => [
                'title'       => 'tiipiik.catalog::lang.component.product_list.param.product_page_id_title',
                'description' => 'tiipiik.catalog::lang.component.product_list.param.product_page_id_desc',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
                'group'       => 'Products',
            ],
            'noProductsMessage' => [
                'title'        => 'tiipiik.catalog::lang.component.product_list.param.no_product_title',
                'description'  => 'tiipiik.catalog::lang.component.product_list.param.no_product_desc',
                'type'         => 'string',
                'default'      => 'No product found',
                'group'       => 'Products'
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
        // $deprecatedSlug = $this->property('productPageIdParam');
        
        // Use strict method only to avoid conflicts whith other plugins
        $this->productPage = $this->property('productPage');
        
        $category = $this->category = $this->loadCategory();
        
        // Return error only if category filter is not used
        if ($this->property('useCategoryFilter') == 0)
        {
            if (!$category)
            {
                $this->setStatusCode(404);
                return $this->controller->run('404');
            }
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

            if ($currentPage > ($lastPage = $products->lastPage()) && $currentPage > 1)
                return Redirect::to($paginationUrl . $lastPage);

            $this->page['paginationUrl'] = $paginationUrl;
        }
        
        $this->noProductsMessage = $this->property('noProductsMessage');
        $this->productParam = $this->property('productParam');
        $this->productPageIdParam = $this->property('categorySlug');
    }
    
    public function listProducts()
    {
        $categories = $this->category ? $this->category->id : null;
        
        if ($this->property('useCategoryFilter') == 1 && $this->property('categoryFilter') != '')
        {
            $category = Category::whereSlug($this->property('categoryFilter'))->first();
            $categories = $category->id;
        }
        
        $products = Product::with('categories')->listFrontEnd([
            'page' => $this->property('pageParam'),
            'perPage' => $this->property('productsPerPage'),
            'categories' => $categories,
        ]);
        
        return $products;
    }
    
    protected function loadCategory()
    {        
        $category = Category::make()->categoryDetails([
            'category' => $this->property('categorySlug'),
        ]);
        
        if (empty($category))
            return null;
            
        $this->page->title = $category->name;
        
        return $category;
    }

}