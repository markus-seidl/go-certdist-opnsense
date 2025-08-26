<?php

namespace OPNsense\GoCertdist;

class LogfileController extends \OPNsense\Base\IndexController
{
    public function indexAction()
    {
        $this->view->pick('OPNsense/GoCertdist/logfile');
    }
}
