<?php namespace Tiipiik\Catalog\Components;

use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Tiipiik\Catalog\Models\Store;
use Tiipiik\Catalog\Models\CustomField;
use Tiipiik\Catalog\Models\Settings;

class StoreDetails extends ComponentBase
{
    public $store;
    public $productPage;
    public $noProductsMessage;

    public function componentDetails()
    {
        return [
            'name'        => 'tiipiik.catalog::lang.component.store_details.name',
            'description' => 'tiipiik.catalog::lang.component.store_details.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'tiipiik.catalog::lang.component.store_details.param.slug_title',
                'description' => 'tiipiik.catalog::lang.component.store_details.param.slug_desc',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'products' => [
                'title'       => 'tiipiik.catalog::lang.component.store_details.param.products_title',
                'description' => 'tiipiik.catalog::lang.component.store_details.param.products_desc',
                'default'     => '0',
                'type'        => 'checkbox',
                'group'       => 'Products',
            ],
            'productPage' => [
                'title'       => 'tiipiik.catalog::lang.component.store_details.param.product_page_title',
                'description' => 'tiipiik.catalog::lang.component.store_details.param.product_page_desc',
                'type'        => 'dropdown',
                'default'     => 'product-details/:slug',
                'group'       => 'Products',
            ],
            'noProductsMessage' => [
                'title'       => 'tiipiik.catalog::lang.component.store_details.param.no_product_message_title',
                'description' => 'tiipiik.catalog::lang.component.store_details.param.no_product_message_desc',
                'type'        => 'string',
                'default'     => 'tiipiik.catalog::lang.component.store_details.param.no_product_message_default',
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
        $store = $this->loadStore();

        if (!$store) {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }

        $this->store = $this->page['store'] = $store;
        
        $this->productPage = $this->property('productPage');
        $this->noProductsMessage = $this->property('noProductsMessage');

        $this->page->title = ($store->meta_title != null)
            ? $store->meta_title
            : $store->title;

        $this->page->description = ($store->meta_desc != null)
            ? $store->meta_desc
            : $store->description;
    }

    protected function loadStore()
    {
        $store = null;
        $slug = $this->property('slug');
        
        $store = Store::whereSlug($slug)->with('customfields')->whereIsActivated(1);
        
        // Do we display related products ?
        if ($this->property('products') == 1) {
            $store = $store->with(['products' => function ($q) {
                $q->whereIsPublished(1);
            }]);
        }
        $store = $store->first();
        
        // Injects related custom fields
        if (isset($store->customfields)) {
            foreach ($store->customfields as $customfield) {
                $fieldId = $customfield['custom_field_id'];
                // Grab custom field template code
                $field = CustomField::find($fieldId);
                $store->attributes[$field->template_code] = $customfield->value;
            }
        }

        if (isset($store->products)) {
            $store->products->each(function ($product) {
                $product->url = (Settings::get('secure_urls') == 1)
                    ? secure_url($this->property('productPage').'/'.$product->slug)
                    : url($this->property('productPage').'/'.$product->slug);
            });
        }
        
        return $store;
    }
}
