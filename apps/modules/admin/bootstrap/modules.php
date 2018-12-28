<?php
/**
 * Register application modules
 */

$application->registerModules(
    [
        'admin'  => [
            'className' => 'Modules\Modules\Admin\Module',
            'path'      => APP_PATH . 'modules/admin/Module.php'
        ],
        'dashboard' => [
            'className' => 'Modules\Modules\Dashboard\Module',
            'path'      => APP_PATH . 'modules/dashboard/Module.php'
        ]
    ]
);
