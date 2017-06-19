<?php namespace Tiipiik\Catalog\Models;

use Model;

/**
 * Settings Model
 */
class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];
    public $settingsCode = 'tiipiik_catalog_settings';
    public $settingsFields = 'fields.yaml';
}
