<?php namespace Tiipiik\Catalog\Components;

use DB;
use BackendMenu;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Tiipiik\Catalog\Models\Category;

class Categories extends ComponentBase
{
    public $categories;
    public $productCategoryPage;
    public $currentProductCategorySlug;
    public $noProductCategoriesMessage;

    public function componentDetails()
    {
        return [
            'name'        => 'Categories',
            'description' => 'Display a list of categories'
        ];
    }

    public function defineProperties()
    {
        return [
            'idParam' => [
                'title'       => 'Slug param name',
                'description' => 'The URL route parameter used for looking up the current category by its slug. This property is used by the default component partial for marking the currently active category.',
                'default'     => ':slug',
                'type'        => 'string'
            ],
            'noProductCategoriesMessage' => [
                'title'        => 'No categories message',
                'description'  => 'Message to display in the categories list in case if there are no categories. This property is used by the default component partial.',
                'type'         => 'string',
                'default'      => 'No categories found'
            ],
            'renderView' => [
                'title'        => 'View',
                'description'  => 'Indicate which partial file of the component should be used to render view.',
                'type'         => 'string',
                'default'      => 'menu_list',
                'group'       => 'Render',
            ],
            'categoryPage' => [
                'title'       => 'Category page',
                'description' => 'Name of the category page file for the category links. This property is used by the default component partial.',
                'type'        => 'dropdown',
                'default'     => 'blog/category',
                'group'       => 'Links',
            ],
        ];
    }
    
    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }
    
    public function onRun()
    {
        $this->render_view                  = $this->property('renderView');
        $this->noProductCategoriesMessage   = $this->property('noCategoriesMessage');
        $this->productCategoryPage          = $this->property('categoryPage');
        $this->currentProductCategorySlug   = $this->propertyOrParam('idParam');
        $this->product_categories           = $this->loadCategories();
    }

    protected function loadCategories()
    {
        $categories = Category::orderBy('name')->get();
        
        if (!$categories)
            return null;

        /*
         * Add a "url" helper attribute for linking to each category
         */
        $categories->each(function($category){
            $category->setUrl($this->productCategoryPage, $this->controller);
        });

        return $categories;
    }

}