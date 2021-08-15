<?php

namespace LaraAreaTranslation\Providers;

use LaraAreaSupport\LaraAreaServiceProvider;

class ServiceProvider extends LaraAreaServiceProvider
{
    /**
     *
     */
    public function boot()
    {
        $this->mergeConfig(__DIR__, 'laraarea_languages');
    }
}

