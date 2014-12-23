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
            'name'        => 'ProductList',
            'description' => 'Display a list of products'
        ];
    }

    public function defineProperties()
    {
        return [
            'productsPerPage' => [
                'title'             => 'Products per page',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Invalid format of the products per page value',
                'default'           => '10',
            ],
            'categoryParam' => [
                'title' => 'Dynamic category',
                'description' => 'Get the category from paramter.',
                'type' => 'string',
                'default' => ':slug'
            ],
            'categoryFilter' => [
                'title' => 'Category filter',
                'description' => 'Select a cateogry to filter the product list by. Leave empty to show all products.',
                'type' => 'string',
                'default' => ''
            ],
            'pageParam' => [
                'title'       => 'Pagination parameter name',
                'description' => 'The expected parameter name used by the pagination pages.',
                'type'        => 'string',
                'default'     => ':page',
            ],
            'productPage' => [
                'title'       => 'Product page',
                'description' => 'Name of the product page file for the "Learn more" links. This property is used by the default component partial.',
                'type'        => 'dropdown',
                'default'     => 'products/:slug'
            ],
            'productPageIdParam' => [
                'title'       => 'Product page param name',
                'description' => 'The expected parameter name used when creating links to the product page.',
                'type'        => 'string',
                'default'     => ':slug',
            ],
            'noProductsMessage' => [
                'title'        => 'No products message',
                'description'  => 'Message to display in the product list in case if there are no products. This property is used by the default component partial.',
                'type'         => 'string',
                'default'      => 'No products found'
            ],
        ];
    }
    
    public function getProductPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }
    
    public function onRun()
    {
        // Use strict method only to avoid conflicts whith other plugins
        self::prepareVars();
        
        $this->noProductsMessage = $this->property('noProductsMessage');
        $this->productParam = $this->property('productParam');
        $this->productPageIdParam = $this->property('productPageIdParam');
    }
    
    public function prepareVars()
    {
        $this->productPage = $this->property('productPage');
        
        $category = $this->category = $this->loadCategory();
        
        if (!$category)
        {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }
        
        $currentPage = post('page');
        $products = $this->products = $this->listProducts($this->category->id, $currentPage);

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
    }
    
    protected function loadCategory()
    {
        $category = Category::make()->categoryDetails([
            'category' => $this->propertyOrParam('categoryParam'),
        ]);
        
        if (empty($category))
            return null;
        
        $this->page->title = $category->name;
        
        return $category;
    }
    
    public function listProducts($categoryId, $currentPage)
    {
        return Product::make()->listFrontEnd([
            'product' => $this->propertyOrParam('productParam'),
            'category' => $categoryId,
            'page' => $currentPage,
            'perPage' => $this->propertyOrParam('productsPerPage')
        ]);
    }

}