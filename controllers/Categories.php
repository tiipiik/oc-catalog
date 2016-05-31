<?php namespace Tiipiik\Catalog\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Tiipiik\Catalog\Models\Category;

/**
 * Categories Back-end Controller
 */
class Categories extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['tiipiik.catalog.manage_categories'];

    public $bodyClass = 'compact-container';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Tiipiik.Catalog', 'catalog', 'categories');
    }

    /**
     * From Benefreke MenuManager plugin
     * Displays the categories items in a tree list view so they can be reordered
     */
    public function reorder()
    {
        // Ensure the correct sidemenu is active
        BackendMenu::setContext('Tiipiik.Catalog', 'catalog', 'reorder');

        $this->pageTitle = 'tiipiik.catalog::lang.categories.reorder';

        $toolbarConfig = $this->makeConfig();
        $toolbarConfig->buttons = '$/tiipiik/catalog/controllers/categories/_reorder_toolbar.htm';

        $this->vars['toolbar'] = $this->makeWidget('Backend\Widgets\Toolbar', $toolbarConfig);
        $this->vars['records'] = Category::make()->getEagerRoot();
    }

    /**
     * From Benefreke MenuManager plugin
     * Update the menu item position
     */
    public function reorder_onMove()
    {
        $sourceNode = Category::find(post('sourceNode'));
        $targetNode = post('targetNode') ? Category::find(post('targetNode')) : null;

        if ($sourceNode == $targetNode) {
            return;
        }

        switch (post('position')) {
            case 'before':
                $sourceNode->moveBefore($targetNode);
                break;
            case 'after':
                $sourceNode->moveAfter($targetNode);
                break;
            case 'child':
                $sourceNode->makeChildOf($targetNode);
                break;
            default:
                $sourceNode->makeRoot();
                break;
        }
    }
}
