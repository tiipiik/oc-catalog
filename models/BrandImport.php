<?php namespace Tiipiik\Catalog\Models;

use Tiipiik\Catalog\Models\Brand;
use Backend\Models\ImportModel;

/**
 * BrandImport Model
 */
class BrandImport extends ImportModel
{
    public $table = 'tiipiik_catalog_brands';

    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [
        'name' => 'required',
        'slug' => 'required',
    ];

    public function importData($results, $sessionKey = null)
    {
        foreach ($results as $row => $data) {
            try {

                if (!$name = array_get($data, 'name')) {
                    $this->logSkipped($row, 'Missing brand name');
                    continue;
                }

                /*
                 * Find or create
                 */
                $brand = Brand::make();

                if ($this->update_existing) {
                    $brand = $this->findDuplicateBrand($data) ?: $brand;
                }

                $brandExists = $brand->exists;

                /*
                 * Set attributes
                 */
                $except = ['id'];

                foreach (array_except($data, $except) as $attribute => $value) {
                    $brand->{$attribute} = $value ?: null;
                }

                $brand->save();

                /*
                 * Log results
                 */
                $brandExists ? $this->logUpdated() : $this->logCreated();
            } catch (\Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }
    }

    protected function findDuplicateBrand($data)
    {
        if ($id = array_get($data, 'id')) {
            return Brand::find($id);
        }

        $name = array_get($data, 'name');
        $brand = Brand::whereName($name);

        if ($slug = array_get($data, 'slug')) {
            $brand->orWhere('slug', $slug);
        }

        return $brand->first();
    }
}
