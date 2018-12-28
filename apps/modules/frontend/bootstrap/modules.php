<?php
/**
 * Register application modules
 */

$application->registerModules(
    [
        'frontend'  => [
            'className' => 'Modules\Modules\Frontend\Module',
            'path'      => APP_PATH . 'modules/frontend/Module.php'
        ],
        'dashboard' => [
            'className' => 'Modules\Modules\Dashboard\Module',
            'path'      => APP_PATH . 'modules/dashboard/Module.php'
        ]
    ]
);
