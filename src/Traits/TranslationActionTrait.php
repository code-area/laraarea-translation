<?php

namespace LaraAreaTranslation\Traits;

trait TranslationActionTrait
{
    protected $actions = [
        'edit',
        'show',
        'translations.index' => [
            'label' => 'Translations'
        ],
        'destroy'
    ];

    /**
     * @param $group
     * @return array
     */
    public function getActions($group = self::PAGINATE_GROUP)
    {
        if ($group == \ConstIndexableGroup::TRANSLATIONS) {
            if ($this->actions) {
                unset($this->actions['translations.index']);
            } else {
                $this->actions = ['edit', 'show', 'destroy'];
            }
        }

        return $this->actions ? [
            'list' => $this->processActions(),
            'is_separate' => false,
            'label' => 'Actions'
        ]
            : [];
    }
}
