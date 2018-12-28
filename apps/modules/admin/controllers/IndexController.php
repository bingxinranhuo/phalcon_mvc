<?php

namespace Modules\Modules\Admin\Controllers;

use Modules\Models\Services\Services;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        echo "<pre/>";
        print_r(Services::getService('User')->getLast()->toArray());
        echo PHP_EOL;
        die;
        try {
            $this->view->users = Services::getService('User')->getLast();
        } catch (\Exception $e) {
            $this->flash->error($e->getMessage());
        }
    }
}
