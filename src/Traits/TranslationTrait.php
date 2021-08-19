<?php

namespace LaraAreaTranslation\Traits;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use LaraAreaTranslation\Translation;

trait TranslationTrait
{
    use LanguageAttributeTrait;

    /**
     * Translationable columns
     *
     * @var
     */
    protected $translateable;

    /**
     * @var int
     */
    protected $translationSource = \ConstTranslationResource::SAME_TABLE;

    /**
     * @var
     */
    protected $translationTable;

    /**
     * Resource translation instance
     *
     * @var
     */
    protected $translation;

    /**
     * @param static| integer $main
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function translationsQuery($model)
    {
        if (is_a($model, static::class)) {
            return $model->translations();
        }

        $self = (new static());
        $foreignColumn = $self->getTranslationForeignColumn();
        if (\ConstTranslationResource::SAME_TABLE == $self->getTranslationSource()) {
            return $self::query()->where($foreignColumn, $model);
        }

        $self->prepareTranslationModel();
        return Translation::query()->where($foreignColumn, $model);
    }

    /**
     * Define all translations relations
     *
     * @return mixed
     */
    public function translations()
    {
        $foreignId = $this->getTranslationForeignColumn();
        if (\ConstTranslationResource::TRANSLATIONS_TABLE == $this->getTranslationSource()) {
            $this->prepareTranslationModel();
            return $this->hasMany(Translation::class, $foreignId);
        }

        return $this->hasMany(get_class($this), $foreignId);
    }

    /**
     * @param bool $makeProperty
     * @param null $language
     * @param array $columns
     * @param null $languageColumn
     * @return $this
     */
    public function translate($makeProperty = true, $language = null, $columns = [], $languageColumn = null)
    {
        $language = $language ?? App::getLocale();
        $languageColumn = $languageColumn ?? $this->getLanguageColumnName();
        $columns = $columns ?: $this->getTranslateAbleColumns();

        $this->translation = $this->getTranslationBy($language, $columns, $languageColumn);

        if ($makeProperty) {
            foreach ($columns as $column) {
                if (key_exists($column, $this->attributes)) {
                    $this->{$column . '_translated'} = $this->translation->{$column} ?? $this->{$column};
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
     * @param $language
     * @param $columns
     * @param $languageColumn
     * @return mixed
     */
    public function getTranslationBy($language, $columns, $languageColumn)
    {
        $language = $language ?? App::getLocale();
        $languageColumn = $languageColumn ?? $this->getLanguageColumnName();
        $columns = $columns ?: $this->getTranslateAbleColumns();

        if (key_exists('translations', $this->relations)) {
            $this->translation = $this->translations->where($languageColumn, $language)->first();

            if (empty($this->translation)) {
                return $this->loadTranslation($language, $columns, $languageColumn);
            }

            return $this->translation;
        }

        return $this->loadTranslation($language, $columns, $languageColumn);
    }

    /**
     * @param $language
     * @param $columns
     * @param $languageColumn
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    protected function loadTranslation($language, $columns, $languageColumn)
    {
        $selectColumns = array_merge($columns, [$this->getKeyName(), $languageColumn]);
        return self::translationsQuery($this)
            ->select($selectColumns)
            ->where($languageColumn, $language)
            ->first();
    }

    /**
     * @param $query
     */
    public function scopeMain($query)
    {
        $foreignId = $this->getTranslationForeignColumn();
        if (\ConstTranslationResource::SAME_TABLE == $this->getTranslationSource()) {
            $query->whereNull($foreignId);
        }
    }

    /**
     * @param bool $setFillable
     */
    protected function prepareTranslationModel($setFillable = true)
    {
        $translationTable = $this->getTranslationTable();
        Translation::setDynamicTable($translationTable);
        if ($setFillable) {
            $fillable = $this->getTranslateAbleColumns();
            $fillable[] = $this->getTranslationForeignColumn();
            $fillable[] = $this->getLanguageColumnName();
            Translation::setDynamicFillable($fillable);
        }
    }

    /**
     * @return int
     */
    public function getTranslationSource()
    {
        return $this->translationSource;
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
        return \ConstTranslationResource::TRANSLATIONS_TABLE == $this->getTranslationSource()
            ? 'translatable_id'
            : 'parent_id';
    }

    /**
     * @return string
     */
    public function getTranslationTable()
    {
        if (is_null($this->translationTable)) {
            $this->translationTable = Str::singular($this->getTable()) . '_translations';
        }

        return $this->translationTable;
    }
}
