<?php
namespace Tiipiik\Catalog\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Flash;
use Tiipiik\Catalog\Models\Property;

/**
 * Properties Back-end Controller
 */
class Properties extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Tiipiik.Catalog', 'catalog', 'properties');
    }

    public function index_onActivate()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $itemId) {
                if ((!$item = Property::find($itemId))) {
                    continue;
                }

                $item->is_used = true;
                $item->save();
            }

            Flash::success(e(trans('tiipiik.catalog::lang.properties.activate_success')));
        }

        return $this->listRefresh();
    }

    public function index_onDeactivate()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $itemId) {
                if ((!$item = Property::find($itemId))) {
                    continue;
                }

                $item->is_used = false;
                $item->save();
            }

            Flash::success(e(trans('tiipiik.catalog::lang.properties.deactivate_success')));
        }

        return $this->listRefresh();
    }
}
