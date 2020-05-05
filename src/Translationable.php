<?php

namespace LaraAreaTranslation;

use Illuminate\Database\Eloquent\Model;
use LaraAreaTranslation\Traits\TranslationTrait;

/**
 * Class Translationable
 * @package LaraAreaTranslation
 */
class Translationable extends Model
{
    use TranslationTrait;
}
