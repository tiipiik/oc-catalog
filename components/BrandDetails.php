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
            'name'        => 'tiipiik.catalog::lang.component.brand_details.name',
            'description' => 'tiipiik.catalog::lang.component.brand_details.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'tiipiik.catalog::lang.component.brand_details.param.slug_title',
                'description' => 'tiipiik.catalog::lang.component.brand_details.param.slug_desc',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'products' => [
                'title'       => 'tiipiik.catalog::lang.component.brand_details.param.products_title',
                'description' => 'tiipiik.catalog::lang.component.brand_details.param.products_desc',
                'default'     => '0',
                'type'        => 'checkbox',
                'group'       => 'Products',
            ],
            'productPage' => [
                'title'       => 'tiipiik.catalog::lang.component.brand_details.param.product_page_title',
                'description' => 'tiipiik.catalog::lang.component.brand_details.param.product_page_desc',
                'type'        => 'dropdown',
                'default'     => 'product-details/:slug',
                'group'       => 'Products',
            ],
            'noProductsMessage' => [
                'title'       => 'tiipiik.catalog::lang.component.brand_details.param.no_product_message_title',
                'description' => 'tiipiik.catalog::lang.component.brand_details.param.no_product_message_desc',
                'type'        => 'string',
                'default'     => 'tiipiik.catalog::lang.component.brand_details.param.no_product_message_default',
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
        $brand = $this->loadBrand();

        if (!$brand) {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }

        $this->brand = $this->page['brand'] = $brand;
        
        $this->productPage = $this->property('productPage');
        $this->noProductsMessage = $this->property('noProductsMessage');
        
        $this->page->title = ($brand->meta_title != null)
            ? $brand->meta_title
            : $brand->title;

        $this->page->description = ($brand->meta_desc != null)
            ? $brand->meta_desc
            : $brand->description;
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
