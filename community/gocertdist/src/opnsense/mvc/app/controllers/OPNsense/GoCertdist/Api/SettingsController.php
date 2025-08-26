<?php

namespace OPNsense\GoCertdist\Api;

use OPNsense\Base\ApiMutableModelControllerBase;
use OPNsense\Core\Backend;

class SettingsController extends ApiMutableModelControllerBase
{
    protected static $internalModelName = 'gocertdist';
    protected static $internalModelClass = 'OPNsense\GoCertdist\GoCertdist';

    /**
     * Reload configuration and regenerate templates
     * @return array
     */
    public function reloadAction()
    {
        $result = array("result" => "failed");

        if ($this->request->isPost()) {
            try {
                $backend = new Backend();

                // Use standard OPNsense template reload
                $backend->configdRun('template reload OPNsense/GoCertdist');

                $result["result"] = "ok";
                $result["message"] = "Configuration reloaded successfully";

            } catch (\Exception $e) {
                $result["result"] = "failed";
                $result["message"] = "Failed to reload configuration: " . $e->getMessage();
            }
        }

        return $result;
    }
}
