<?php namespace Tiipiik\Catalog\Components;

use Request;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Tiipiik\Catalog\Models\Store;

class StoreList extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Store List',
            'description' => 'Display a list of stores.'
        ];
    }

    public function defineProperties()
    {
        return [
            'storePage' => [
                'title'       => 'Page for store details',
                'description' => '',
                'type'        => 'dropdown',
                'default'     => 'stores/:slug',
            ],
            'storeSlug' => [
                'title'       => 'Store slug param',
                'description' => '',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'noStoreMessage' => [
                'title'        => 'No store message',
                'description'  => '',
                'type'         => 'string',
                'default'      => 'No store found',
            ],
            'storesPerPage' => [
                'title'             => 'Stores per page',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => '',
                'default'           => '9',
                'group'             => 'Pagination',
            ],
            'pageParam' => [
                'title'       => 'Page param',
                'description' => '',
                'type'        => 'string',
                'default'     => '{{ :page }}',
                'group'       => 'Pagination',
            ],
        ];
    }
    
    
    public function getStorePageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
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

            if ($currentPage > ($lastPage = $stores->lastPage()) && $currentPage > 1)
                return Redirect::to($paginationUrl . $lastPage);

            $this->page['paginationUrl'] = $paginationUrl;
        }
        
        $this->noStoreMessage = $this->property('noStoreMessage');
        $this->storeSlug = $this->property('storeSlug');
    }
    
    
    public function listStores()
    {
        $stores = Store::listFrontEnd([
            'page' => $this->property('storeSlug'),
            'perPage' => $this->property('storesPerPage'),
        ]);
        
        return $stores;
    }

}