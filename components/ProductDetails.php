<?php namespace Tiipiik\Catalog\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Tiipiik\Catalog\Models\CustomField;
use Tiipiik\Catalog\Models\Category;
use Tiipiik\Catalog\Models\Settings;
use Tiipiik\Catalog\Models\Product as ProductModel;

class ProductDetails extends ComponentBase
{
    protected $product;
    protected $categoryPage;
    protected $brandPage;
    protected $storePage;

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
            'brandPage' => [
                'title'       => 'Brand',
                'description' => 'desc',
                'type'        => 'dropdown',
                'group'       => 'Links',
            ],
            'storePage' => [
                'title'       => 'Store',
                'description' => 'desc',
                'type'        => 'dropdown',
                'group'       => 'Links',
            ],
        ];
    }
    
    public function getCategoryPageOptions()
    {
        return [''=>'- Select page -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getBrandPageOptions()
    {
        return [''=>'- Select page -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getStorePageOptions()
    {
        return [''=>'- Select page -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
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
        $this->storePage = $this->page['storePage'] = $this->property('storePage');
        $this->product = $this->page['product'] = $product;
        
        $this->page->title = ($product->meta_title != null)
            ? $product->meta_title
            : $product->title;

        $this->page->description = ($product->meta_desc != null)
            ? $product->meta_desc
            : $product->description;
    }

    protected function loadProduct()
    {
        $slug = $this->property('slug');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->brandPage = $this->page['brandPage'] = $this->property('brandPage');
        $this->storePage = $this->page['storePage'] = $this->property('storePage');
        $this->secureUrls = $this->page['secureUrls'] = $this->property('secureUrls');
        
        $product = ProductModel::whereSlug($slug)
            ->whereIsPublished(1)
            ->with('customfields')
            ->with('categories')
            ->with('brand')
            ->with('stores')
            ->first();

        if (isset($product->categories)) {
            $product->categories->each(function ($category) {
                $category->url = (Settings::get('secure_urls') == 1)
                    ? secure_url($this->categoryPage.'/'.$category->slug)
                    : url($this->categoryPage.'/'.$category->slug);
            });
        }

        if (isset($product->stores)) {
            $product->stores->each(function ($store) {
                $store->url = (Settings::get('secure_urls') == 1)
                    ? secure_url($this->storePage.'/'.$store->slug)
                    : url($this->storePage.'/'.$store->slug);
            });
        }

        if (isset($product->brand)) {
            $product->brand->url = (Settings::get('secure_urls') == 1)
                ? secure_url($this->brandPage.'/'.$product->brand->slug)
                : url($this->brandPage.'/'.$product->brand->slug);
        }

        if (isset($product->customfields)) {
            foreach ($product->customfields as $customfield) {
                $fieldId = $customfield['custom_field_id'];
                // Grab custom field template code
                $field = CustomField::find($fieldId);
                $product->attributes[$field->template_code] = $customfield->value;
                $product->attributes['customfields'][$field->display_name] = $customfield->value;
            }
        }
        
        return $product;
    }
}
