<?php namespace Tiipiik\Catalog\Models;

use Backend\Models\ImportModel;
use Tiipiik\Catalog\Models\Product;

class ProductImport extends ImportModel
{
    public $table = 'tiipiik_catalog_products';

    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [
        'title' => 'required',
        'slug' => 'required',
    ];

    public function importData($results, $sessionKey = null)
    {
        foreach ($results as $row => $data) {
            try {

                if (!$title = array_get($data, 'title')) {
                    $this->logSkipped($row, 'Missing product title');
                    continue;
                }

                /*
                 * Find or create
                 */
                $product = Product::make();

                if ($this->update_existing) {
                    $product = $this->findDuplicateProduct($data) ?: $product;
                }

                $productExists = $product->exists;

                /*
                 * Set attributes
                 */
                $except = ['id'];

                foreach (array_except($data, $except) as $attribute => $value) {
                    $product->{$attribute} = $value ?: null;
                }

                $product->save();

                /*
                 * Log results
                 */
                $productExists ? $this->logUpdated() : $this->logCreated();
            } catch (\Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }
    }

    protected function findDuplicateProduct($data)
    {
        if ($id = array_get($data, 'id')) {
            return Product::find($id);
        }

        $title = array_get($data, 'name');
        $product = Product::whereTitle($title);

        if ($slug = array_get($data, 'slug')) {
            $product->orWhere('slug', $slug);
        }

        return $product->first();
    }
}
