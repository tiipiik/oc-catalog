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
            'name'        => 'Store details',
            'description' => 'Display details about a store.'
        ];
    }


    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'Store slug',
                'description' => 'Parameter used to find store from it\'s slug',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'products' => [
                'title'       => 'Display products',
                'description' => 'Add products related to this store in the view.',
                'default'     => '0',
                'type'        => 'checkbox',
                'group'       => 'Products',
            ],
            'productPage' => [
                'title'       => 'Page for product details',
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
        $this->store = $this->page['store'] = $this->loadStore();
        
        if (!$this->store) {
            // The line below works but return a line of details
            //return Response::make( $this->controller->run('404'), 404 );
            // Use this instead
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }
        
        $this->productPage = $this->property('productPage');
        $this->noProductsMessage = $this->property('noProductsMessage');
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
