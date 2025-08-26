<?php

namespace OPNsense\GoCertdist;

use OPNsense\Core\Config;
use OPNsense\GoCertdist\GoCertdist;

class IndexController extends \OPNsense\Base\IndexController
{
    public function indexAction()
    {
        $this->view->generalForm = $this->getForm("general");
        $this->view->pick('OPNsense/GoCertdist/index');
    }
}
