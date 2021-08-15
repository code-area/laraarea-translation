<?php

namespace LaraAreaTranslation\Traits;

trait LanguageAttributeTrait
{
    /**
     * @var
     */
    protected $languageColumn = 'lang';

    /**
     * @return mixed|string
     */
    public function getLanguageLabelAttribute()
    {
        $languages = collect(config('laraarea_languages'))->pluck('language', 'iso2');
        $languageName = $this->getLanguageColumnName();
        $language = $this->{$languageName};
        return $languages[$language] ?? 'Unknown';
    }

    /**
     * @return string
     */
    public function getLanguageColumnName()
    {
        return $this->languageColumn;
    }
}
