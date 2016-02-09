<?php namespace Tiipiik\Catalog\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Tiipiik\Catalog\Models\Brand;
use Tiipiik\Catalog\Models\Settings;

class BrandDetails extends ComponentBase
{
    public $brand;
    public $productPage;
    public $noProductsMessage;

    public function componentDetails()
    {
        return [
            'name'        => 'Brand Details',
            'description' => 'Display details of selected brand'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'Brand slug',
                'description' => 'Parameter used to find brand from it\'s slug',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'products' => [
                'title'       => 'Display products',
                'description' => 'Add products related to this brand in the view.',
                'default'     => '0',
                'type'        => 'checkbox',
                'group'       => 'Products',
            ],
            'productPage' => [
                'title'       => 'Page for products details',
                'description' => '',
                'type'        => 'dropdown',
                'default'     => 'product-details/:slug',
                'group'       => 'Products',
            ],
            'noProductsMessage' => [
                'title'       => 'Message if no products',
                'description' => '',
                'type'        => 'string',
                'default'     => 'No product related to this brand',
                'group'       => 'Products',
            ],
        ];
    }
    
    public function getProductPageOptions()
    {
        return [''=>'- Select page -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->brand = $this->page['brand'] = $this->loadBrand();
        
        if (!$this->brand) {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }
        
        $this->productPage = $this->property('productPage');
        $this->noProductsMessage = $this->property('noProductsMessage');
    }

    protected function loadBrand()
    {
        $brand = null;
        $slug = $this->property('slug');
        
        $brand = Brand::whereSlug($slug)->wherePublished(1);
        
        // Do we display related products ?
        if ($this->property('products') == 1) {
            $brand = $brand->with(['products' => function ($q) {
                $q->whereIsPublished(1);
            }]);
        }
        $brand = $brand->first();

        if (isset($brand->products)) {
            $brand->products->each(function ($product) {
                $product->url = (Settings::get('secure_urls') == 1)
                    ? secure_url($this->property('productPage').'/'.$product->slug)
                    : url($this->property('productPage').'/'.$product->slug);
            });
        }
        
        return $brand;
    }
}
