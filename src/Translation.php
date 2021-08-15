<?php

namespace LaraAreaTranslation;

use Illuminate\Database\Eloquent\Model;
use LaraAreaTranslation\Traits\LanguageAttributeTrait;

/**
 * Class Translation
 * @package LaraAreaTranslation
 */
class Translation extends Model
{
    use LanguageAttributeTrait;

    /**
     * @var
     */
    public static $staticTable;

    /**
     * @var
     */
    public static $staticFillable = [];

    /**
     * @return string
     */
    public function getTable()
    {
        return self::$staticTable;
    }

    /**
     * @return array
     */
    public function getFillable()
    {
        return self::$staticFillable;
    }

    /**
     * @param $table
     */
    public static function setDynamicTable($table)
    {
        self::$staticTable = $table;
    }

    /**
     * @param $fillable
     */
    public static function setDynamicFillable($fillable)
    {
        self::$staticFillable = $fillable;
    }
}
