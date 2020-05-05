<?php

namespace LaraAreaTranslation;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Translation
 * @package LaraAreaTranslation
 */
class Translation extends Model
{
    /**
     * @var
     */
    public static $staticTable;

    /**
     * @var
     */
    public static $staticFillable;

    /**
     * @return string
     */
    public function getTable()
    {
        return self::$staticTable;
    }

    /**
     * @return string
     */
    public function getFillable()
    {
        return self::$staticFillable;
    }

    /**
     * @param $table
     */
    public static function setStaticTable($table)
    {
        self::$staticTable = $table;
    }

    /**
     * @param $fillable
     */
    public static function setStaticFillable($fillable)
    {
        self::$staticFillable = $fillable;
    }
}
