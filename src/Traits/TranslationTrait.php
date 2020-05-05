<?php

namespace LaraAreaTranslation\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use LaraAreaTranslation\Translation;

trait TranslationTrait
{
    /**
     * Translationable columns
     *
     * @var
     */
    protected $translateable;

    /**
     * @var string
     */
    protected $translationPrimaryKey = 'id';

    /**
     * @var int
     */
    protected $translationSource = \ConstTranslationResource::SAME_TABLE;

    /**
     * @var
     */
    protected $translationForeignColumn;

    /**
     * @var
     */
    protected $translationTable;

    /**
     * @var
     */
    protected $language;

    /**
     * @var
     */
    protected $languageColumn = 'language';

    /**
     * Resource translation instance
     *
     * @var
     */
    protected $translation;

    /**
     * Define all translations relations
     *
     * @return mixed
     */
    public function translations()
    {
        $foreignId = $this->getTranslationForeignColumn();
        if (\ConstTranslationResource::TRANSLATIONS_TABLE == $this->getTranslationSource()) {
            $translationTable = $this->getTranslationTable();
            Translation::setStaticTable($translationTable);
            return $this->hasMany(Translation::class, $foreignId);
        }

        return $this->hasMany(get_class($this), $foreignId);
    }

    public function translationQuery()
    {
        $translationTable = $this->getTranslationTable();
        Translation::setStaticTable($translationTable);
        return Translation::query();
    }

    /**
     * @param null $language
     * @param array $columns
     * @param bool $makeDefaultProperty
     * @return $this
     */
    public function translate($language = null, $columns = [], $makeDefaultProperty = true)
    {
        $language = $language ?? $this->getLanguage();
        $columns = $columns ? $columns : $this->getTranslateAbleColumns();
        $languageColumn = $languageColumn ?? $this->getLanguageColumn();
        $translation = $this->getTranslationBy($columns, $language, $languageColumn);

        if ($makeDefaultProperty) {
            foreach ($columns as $column) {
                if (key_exists($column, $this->attributes)) {
                    $this->{$column . '_default'} = $translation->{$column} ?? $this->{$column};
                }
            }
            return $this;
        }

        foreach ($columns as $column) {
            if (key_exists($column, $this->attributes)) {
                $this->{$column} = $translation->{$column} ?? $this->{$column};
            }
        }

        return $this;
    }

    /**
     * @param $columns
     * @param $language
     * @param $languageColumn
     * @return mixed
     */
    public function getTranslationBy($columns, $language, $languageColumn)
    {
        $language = $language ?? $this->getLanguage();
        $columns = $columns ?? $this->getTranslateAbleColumns();
        $languageColumn = $languageColumn ?? $this->getLanguageColumn();

        if (key_exists('translations', $this->relations)) {
            $this->translation = $this->translations
                ->when($language, function ($items) use ($languageColumn, $language) {
                    return $items->where($languageColumn, $language);
                })
                ->first();
            if (empty($this->translation)) {
                return $this->loadTranslation($columns, $language, $languageColumn);
            }

            return $this->translation;
        }

        return $this->loadTranslation($columns, $language, $languageColumn);
    }

    /**
     * @return mixed
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * @param $columns
     * @param $language
     * @param $languageColumn
     * @return mixed
     */
    protected function loadTranslation($columns, $language, $languageColumn)
    {
        $selectColumns = array_merge($columns, [$this->getTranslationKeyName(), $languageColumn]);
        $translations = $this->translations()
            ->select($selectColumns)
            ->when($language, function ($q) use ($languageColumn, $language) {
                $q->where($languageColumn, $language);
            })
            ->limit(1)
            ->get();

        $this->translation = $language
            ? $translations->where($languageColumn, $language)->first()
            : $this->translation = $translations->first();

        return $this->translation;
    }

    /**
     * Get all translateable cols
     *
     * @return mixed
     */
    public function getTranslateAbleColumns()
    {
        return $this->translateable ?? $this->getFillable();
    }

    /**
     * @param $columns
     * @return array
     */
    public function getTranslateAbleColumnsWith($columns)
    {
        return array_merge($columns, $this->getTranslateAbleColumns());
    }


    /**
     * @return string
     */
    public function getTranslationForeignColumn()
    {
        return \ConstTranslationResource::TRANSLATIONS_TABLE == $this->translationSource
            ? 'translatable_id'
            : 'parent_id';
    }

    /**
     * @return string
     */
    public function getTranslationTable()
    {
        if (is_null($this->translationTable)) {
            return Str::singular($this->getTable()) . '_translations';
        }

        return $this->translationTable;
    }

    /**
     * @return string
     */
    public function getLanguageColumn()
    {
        return $this->languageColumn;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language ?? App::getLocale();
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getTranslationKeyName()
    {
        return $this->translationPrimaryKey;
    }

    public function getTranslationSource()
    {
        return $this->translationSource;
    }
}
