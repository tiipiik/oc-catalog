<?php namespace Tiipiik\Catalog\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Tiipiik\Catalog\Models\CustomField;

/**
 * CustomFields Back-end Controller
 */
class CustomFields extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $bodyClass = 'compact-container';

    public $requiredPermissions = ['tiipiik.catalog.manage_custom_fields'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Tiipiik.Catalog', 'catalog', 'customfields');
    }

    /*
     * Check if fields are passde for deletion
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $customFieldId) {
                if ((!$customField = CustomField::find($customFieldId))) {
                    continue;
                }

                $customField->delete();
            }

            Flash::success(e(trans('tiipiik.catalog::lang.custom_fields.delete_success')));
        }

        return $this->listRefresh();
    }
}
