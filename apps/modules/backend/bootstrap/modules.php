<?php
/**
 * Register application modules
 */

$application->registerModules(
    [
        'backend'  => [
            'className' => 'Modules\Modules\Backend\Module',
            'path'      => APP_PATH . 'modules/backend/Module.php'
        ],
        'dashboard' => [
            'className' => 'Modules\Modules\Dashboard\Module',
            'path'      => APP_PATH . 'modules/dashboard/Module.php'
        ]
    ]
);
