<?php namespace Tiipiik\Catalog\Components;

use Request;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Tiipiik\Catalog\Models\Store;
use Tiipiik\Catalog\Models\CustomField;

class StoreList extends ComponentBase
{
    public $stores;
    public $storePage;

    public function componentDetails()
    {
        return [
            'name'        => 'tiipiik.catalog::lang.component.store_list.name',
            'description' => 'tiipiik.catalog::lang.component.store_list.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'storePage' => [
                'title'       => 'tiipiik.catalog::lang.component.store_list.param.store_page_title',
                'description' => 'tiipiik.catalog::lang.component.store_list.param.store_page_desc',
                'type'        => 'dropdown',
                'default'     => 'stores/:slug',
            ],
            'storeSlug' => [
                'title'       => 'tiipiik.catalog::lang.component.store_list.param.store_slug_title',
                'description' => 'tiipiik.catalog::lang.component.store_list.param.store_slug_desc',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'noStoreMessage' => [
                'title'        => 'tiipiik.catalog::lang.component.store_list.param.no_store_message_title',
                'description'  => 'tiipiik.catalog::lang.component.store_list.param.no_store_message_desc',
                'type'         => 'string',
                'default'      => 'tiipiik.catalog::lang.component.store_list.param.no_store_message_default',
            ],
            'storesPerPage' => [
                'title'             => 'tiipiik.catalog::lang.component.store_list.param.stores_per_page_title',
                'description'       => 'tiipiik.catalog::lang.component.store_list.param.stores_per_page_desc',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => '',
                'default'           => '9',
                'group'             => 'Pagination',
            ],
            'pageParam' => [
                'title'       => 'tiipiik.catalog::lang.component.store_list.param.page_param_title',
                'description' => 'tiipiik.catalog::lang.component.store_list.param.page_param_desc',
                'type'        => 'string',
                'default'     => '{{ :page }}',
                'group'       => 'Pagination',
            ],
        ];
    }
    
    
    public function getStorePageOptions()
    {
        return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }
    
    public function onRun()
    {
        // Use strict method only to avoid conflicts whith other plugins
        $this->storePage = $this->property('storePage');
        
        $currentPage = post('page');
        $stores = $this->stores = $this->listStores();
        
        /*
         * Pagination
         */
        if ($stores) {
            $queryArr = [];
            $queryArr['page'] = '';
            $paginationUrl = Request::url() . '?' . http_build_query($queryArr);

            if ($currentPage > ($lastPage = $stores->lastPage()) && $currentPage > 1) {
                return Redirect::to($paginationUrl . $lastPage);
            }

            $this->page['paginationUrl'] = $paginationUrl;
        }
        
        $this->noStoreMessage = $this->property('noStoreMessage');
        $this->storeSlug = $this->property('storeSlug');
    }
    
    public function listStores()
    {
        $stores = Store::with('customfields')->listFrontEnd([
            'page' => $this->property('storeSlug'),
            'perPage' => $this->property('storesPerPage'),
        ]);
        
        // Injects related custom fields
        $stores->each(function ($store) {
            if ($store->customfields) {
                foreach ($store->customfields as $customfield) {
                    $fieldId = $customfield['custom_field_id'];
                    // Grab custom field template code
                    $field = CustomField::find($fieldId);
                    $store->attributes[$field->template_code] = $customfield->value;
                }
            }
        });
        
        return $stores;
    }
}
