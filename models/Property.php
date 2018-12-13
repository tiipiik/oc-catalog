<?php
namespace Tiipiik\Catalog\Models;

use Carbon\Carbon;
use Model;

/**
 * Property Model
 */
class Property extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'tiipiik_catalog_properties';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'name',
        'type',
        'description',
        'values_array',
        'is_used',
    ];

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'products' => [
            '\Tiipiik\Catalog\Models\Product',
            'table' => 'tiipiik_catalog_prods_props',
            'order' => 'name',
            'pivotModel' => '\TiipiiK\Catalog\Classes\PropertyPivot',
            'pivot' => ['value'],
        ],
    ];

    /**
     * @param $query
     * @return mixed \October\Rain\Database\QueryBuilder
     */
    public function scopeIsUsed($query)
    {
        return $query->where('is_used', true);
    }

    /**
     * Type dropdown options
     * @return array
     */
    public function getTypeOptions()
    {
        return [
            1 => 'tiipiik.catalog::lang.properties.type_numeric',
            2 => 'tiipiik.catalog::lang.properties.type_string',
            3 => 'tiipiik.catalog::lang.properties.type_dropdown',
        ];
    }

    /**
     * Returns raw options array for dropdown field in relation controller
     * @return array
     */
    public function getPivotValueOptions()
    {
        return $this->values_array;
    }

    /**
     * Returns the final property value from pivot data using values array if needed
     * @return mixed
     */
    public function getFinalValue()
    {
        if ($this->pivot) {
            switch ($this->type) {
                case 2:
                    if ($this->pivot->value && strtotime($this->pivot->value)) {
                        $carbon = new Carbon($this->pivot->value);
                        return $carbon->diffForHumans();
                    } else {
                        return $this->pivot->value;
                    }
                case 3:
                    return isset($this->getPivotValueOptions()[$this->pivot->value]) ? $this->getPivotValueOptions()[$this->pivot->value] : $this->pivot->value;
                default:
                    return $this->pivot->value;

            }
        } else {
            return null;
        }
    }

    /**
     * Virtual attribute used to output type in columns
     * @return string
     */
    public function getTypeTextAttribute()
    {
        return e(trans($this->getTypeOptions()[$this->type]));
    }

    public function getPivotValueAttribute()
    {
        return $this->getFinalValue();
    }

    /**
     * Attribute mutator for saving values to raw array from repeater
     * @param $values
     */
    public function setValuesRepeaterAttribute($values)
    {
        $array = [];

        foreach ($values as $value) {
            $array[$value['id']] = $value['value'];
        }
        $this->attributes['values_array'] = json_encode($array);
    }

    /**
     * Attribute accessor to convert raw values array to Repeater readable
     * @return mixed
     */
    public function getValuesRepeaterAttribute()
    {
        if (isset($this->attributes['values_array'])) {
            $values = json_decode($this->attributes['values_array'], true);
        } else {
            return null;
        }

        $array = [];

        foreach ($values as $key => $value) {
            $array[] = ['id' => $key, 'value' => $value];
        }

        return $array;
    }

    /**
     * Accessor for retrieving json values
     * @param $values
     * @return mixed
     */
    public function getValuesArrayAttribute($values)
    {
        $values = json_decode($values, true);

        return $values;
    }

    /**
     * Mutator for saving json values
     * @param $values
     */
    public function setValuesArrayAttribute($values)
    {
        $this->attributes['values_array'] = json_encode($values);
    }

    /**
     * @param $fields
     * @param $context
     */
    public function filterFields($fields, $context = null)
    {
        $pivotField = 'pivot[value]';

        if (property_exists($fields, $pivotField)) {
            switch ($this->type) {
                case 1:
                    $fields->{$pivotField}->type = 'number';
                    break;
                case 3:
                    $fields->{$pivotField}->type = 'dropdown';
                    $fields->{$pivotField}->options = $this->getPivotValueOptions();
                    break;
                default:
                    $fields->{$pivotField}->type = 'text';
            }
        }

        if (property_exists($fields, 'values_repeater')) {
            if (property_exists($fields, 'type') && $fields->type->value == 3) {
                $fields->values_repeater->hidden = false;
            } else {
                $fields->values_repeater->hidden = true;
            }
        }
    }

    public function beforeSave()
    {
        if ($this->type != 3) {
            $this->values_repeater = [];
        }
    }
}
