<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Layout Wrapper
    |--------------------------------------------------------------------------
    | The default Blade view used to wrap widgets that return raw data.
    | Themes can override this globally or per-widget using ->wrapWith().
    */
    'default_wrapper' => 'utilities::widgets.default-wrapper',

    'zones' => [
        // Example configuration:
        // 'dashboard_top' => [
        //     [
        //         'widget'   => \App\Widgets\RevenueWidget::class,
        //         'title'    => 'Total Revenue',
        //         'color'    => 'green',
        //         'icon'     => 'currency-dollar',
        //         'priority' => 10,
        //     ],
        // ],
    ],
];