<?php

namespace Axxon\EditionFeatures;

use App\Nova\AxxonOneEdition;
use App\Nova\EditionsFeature;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class EditionFeatures extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('edition-features', __DIR__ . '/../dist/js/tool.js');
        Nova::style('edition-features', __DIR__ . '/../dist/css/tool.css');
    }

    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return \Illuminate\View\View
     */
    public function renderNavigation()
    {
        $resources = [
            new AxxonOneEdition(new \App\Models\AxxonOneEdition()),
            new EditionsFeature(new \App\Models\EditionsFeature()),
        ];

        return view('edition-features::navigation', [
            'resources' => $resources
        ]);
    }
}
