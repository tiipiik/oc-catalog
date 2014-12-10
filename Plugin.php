<?php namespace Tiipiik\Catalog;

use Backend;
use System\Classes\PluginBase;

/**
 * Catalog Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Catalog',
            'description' => 'Provide a full catalog',
            'author'      => 'Tiipiik',
            'icon'        => 'icon-th'
        ];
    }

    public function registerPermissions()
    {
        return [
            'tiipiik.catalog.access_categories' => ['label' => 'tiipiik.catalog::lang.settings.access_categories'],
            'tiipiik.catalog.access_products' => ['label' => 'tiipiik.catalog::lang.settings.access_products'],
            'tiipiik.catalog.access_custom_fields' => ['label' => 'tiipiik.catalog::lang.settings.access_custom_fields']
        ];
    }

    public function registerNavigation()
    {
        return [
            'catalog' => [
                'label'       => 'tiipiik.catalog::lang.plugin_name',
                'url'         => Backend::url('tiipiik/catalog/products'),
                'icon'        => 'icon-th',
                'permissions' => ['tiipiik.catalog.manage_products'],
                'order'       => 20,

                'sideMenu' => [
                    'products' => [
                        'label'       => 'tiipiik.catalog::lang.products.menu_label',
                        'icon'        => 'icon-th',
                        'url'         => Backend::url('tiipiik/catalog/products'),
                        'attributes'  => ['data-menu-item'=>'products'],
                        'permissions' => ['tiipiik.catalog.manage_products'],
                    ],
                    'customfields' => [
                        'label'       => 'tiipiik.catalog::lang.custom_fields.menu_label',
                        'icon'        => 'icon-list-alt',
                        'url'         => Backend::url('tiipiik/catalog/customfields'),
                        'attributes'  => ['data-menu-item'=>'custom_fields'],
                        'permissions' => ['tiipiik.catalog.manage_custom_fields'],
                    ],
                    'categories' => [
                        'label'       => 'tiipiik.catalog::lang.categories.menu_label',
                        'icon'        => 'icon-list-ul',
                        'url'         => Backend::url('tiipiik/catalog/categories'),
                        'attributes'  => ['data-menu-item'=>'categories'],
                        'permissions' => ['tiipiik.catalog.manage_categories'],
                    ],
                    'reorder' => [
                        'label'       => 'tiipiik.catalog::lang.categories.reorder_category',
                        'icon'        => 'icon-exchange',
                        'url'         => Backend::url('tiipiik/catalog/categories/reorder'),
                        'attributes'  => ['data-menu-item'=>'categories'],
                        'permissions' => ['tiipiik.catalog.manage_categories'],
                    ],
                ]

            ]
        ];
    }

}
