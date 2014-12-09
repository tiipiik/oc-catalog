<?php namespace Tiipiik\Catalog;

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
            'description' => 'No description provided yet...',
            'author'      => 'Tiipiik',
            'icon'        => 'icon-leaf'
        ];
    }

}
