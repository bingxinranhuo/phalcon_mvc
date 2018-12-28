<?php
/**
 * Register application modules
 */

$application->registerModules(
    [
        'api'  => [
            'className' => 'Modules\Modules\Api\Module',
            'path'      => APP_PATH . 'modules/api/Module.php'
        ],
        'dashboard' => [
            'className' => 'Modules\Modules\Dashboard\Module',
            'path'      => APP_PATH . 'modules/dashboard/Module.php'
        ]
    ]
);
