<?php namespace Tiipiik\Catalog\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Tiipiik\Catalog\Models\CustomField;
use Tiipiik\Catalog\Models\Category;
use Tiipiik\Catalog\Models\Product as ProductModel;

class ProductDetails extends ComponentBase
{
    protected $product;

    protected $categoryPage;

    protected $secureUrls;

    public function componentDetails()
    {
        return [
            'name'        => 'tiipiik.catalog::lang.component.product_details.name',
            'description' => 'tiipiik.catalog::lang.component.product_details.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'tiipiik.catalog::lang.component.product_details.param.id_param_title',
                'description' => 'tiipiik.catalog::lang.component.product_details.param.id_param_desc',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            'categoryPage' => [
                'title'       => 'tiipiik.catalog::lang.component.categories.param.category_page_title',
                'description' => 'tiipiik.catalog::lang.component.categories.param.category_page_desc',
                'type'        => 'dropdown',
                'default'     => 'category',
                'group'       => 'Links',
            ],
            'secureUrls' => [
                'title'       => 'tiipiik.catalog::lang.settings.use_secure_urls',
                'description' => 'tiipiik.catalog::lang.settings.use_secure_urls_desc',
                'type'        => 'checkbox',
                'default'     => 'false',
                'group'       => 'Links',
            ],
        ];
    }
    
    public function getCategoryPageOptions()
    {
        return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $product = $this->loadProduct();
        
        if (!$product) {
            // The line below works but return a line of details
            //return Response::make( $this->controller->run('404'), 404 );
            // Use this instead
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }
        
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->product = $this->page['product'] = $product;
        
        $this->page->title = $product->title;
        $this->page->description = $product->description;
    }

    protected function loadProduct()
    {
        $slug = $this->property('slug');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->secureUrls = $this->page['secureUrls'] = $this->property('secureUrls');
        
        $product = ProductModel::whereSlug($slug)
            ->with('categories')
            ->with('customfields')
            ->whereIsPublished(1)
            ->first();

        if (isset($product->categories)) {
            $product->categories->each(function($category) {
                $category->url = ($this->secureUrls)
                    ? secure_url($this->categoryPage.'/'.$category->slug)
                    : url($this->categoryPage.'/'.$category->slug);
            });
        }

        if (isset($product->customfields)) {
            foreach ($product->customfields as $customfield) {
                $fieldId = $customfield['custom_field_id'];
                // Grab custom field template code
                $field = CustomField::find($fieldId);
                $product->attributes[$field->template_code] = $customfield->value;
            }
        }
        
        return $product;
    }

}