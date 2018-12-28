<?php

namespace Modules\Modules\Api\Controllers;

use Modules\Models\Services\Services;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        echo json_encode([0,'ok']);
    }
}
