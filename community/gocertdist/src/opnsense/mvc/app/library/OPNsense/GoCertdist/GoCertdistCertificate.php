<?php

namespace OPNsense\GoCertdist;

require_once("script/load_phalcon.php");

use OPNsense\Core\Config;
use OPNsense\Core\Backend;
use OPNsense\Trust\Ca;
use OPNsense\Trust\Cert;
use OPNsense\Trust\Store as CertStore;

class GoCertdistCertificate
{
    private function log($message)
    {
        syslog(LOG_INFO, "gocertdist: " . $message);
    }

    private function log_error($message)
    {
        syslog(LOG_ERR, "gocertdist: " . $message);
    }

    public function import($chain_path, $key_path)
    {
        // Reload to get most recent config
        Config::getInstance()->forceReload();

        // Check if certificate files can be found
        clearstatcache(); // don't let the cache fool us
        foreach (array($key_path, $chain_path) as $file) {
            if (!is_file($file)) {
                $this->log_error("unable to import certificate, file not found: {$file}");
                return false;
            }
        }

        /**
         * Step 1: import CA
         */

        // Read contents from CA file
        $ca_content = @file_get_contents($chain_path);
        if ($ca_content != false) {
            $ca_details = CertStore::parseX509($ca_content);
            $ca_subject = $ca_details['name'];
            $ca_cn = $ca_details['commonname'];
            $ca_issuer = implode(",", $ca_details['issuer']);
        } else {
            $this->log_error('unable to read CA certificate content from file');
            return false;
        }

        // Prepare CA
        $caModel = new Ca();
        $ca = array();
        $ca['refid'] = uniqid();
        $ca['descr'] = (string)$ca_cn . ' (GoCertdist)';
        $ca_found = false;

        // Check if CA was previously imported
        foreach ($caModel->ca->iterateItems() as $cacrt) {
            $cacrt_content = base64_decode((string)$cacrt->crt);
            $cacrt_details = CertStore::parseX509($cacrt_content);
            $cacrt_subject = $cacrt_details['name'];
            $cacrt_issuer = implode(",", $cacrt_details['issuer']);
            if (($ca_subject === $cacrt_subject) && ($ca_issuer === $cacrt_issuer)) {
                $ca['refid'] = (string)$cacrt->refid;
                $cacrt->descr = $ca['descr'];
                $ca_found = true;
                break;
            }
        }

        if ($ca_found == false) {
            $this->log("imported GoCertdist CA: {$ca_cn} ({$ca['refid']})");
            $newca = $caModel->ca->Add();
            foreach (array_keys($ca) as $cacfg) {
                $newca->$cacfg = (string)$ca[$cacfg];
            }
            $newca->crt = base64_encode($ca_content);
        }

        $caModel->serializeToConfig();
        Config::getInstance()->save();

        /**
         * Step 2: import certificate
         */

        // Read contents from certificate file
        $cert_content = @file_get_contents($chain_path);
        if ($cert_content != false) {
            $cert_details = CertStore::parseX509($cert_content);
            $cert_cn = $cert_details['commonname'];
        } else {
            $this->log_error('unable to read certificate content from file');
            return false;
        }

        // Read private key
        $key_content = @file_get_contents($key_path);
        if ($key_content == false) {
            $this->log_error('unable to read private key from file: ' . $key_path);
            return false;
        }

        // Prepare certificate
        $certModel = new Cert();
        $cert = array();
        $cert['caref'] = (string)$ca['refid'];
        $cert['descr'] = 'GoCertdist-Auto'; // Use a fixed name for easy identification
        $import_log_message = 'imported';
        $cert_found = false;
        $cert_refid_to_update = null;

        // Check if cert was previously imported by searching for its description.
        foreach ($certModel->cert->iterateItems() as $cfgCert) {
            if ((string)$cfgCert->descr == $cert['descr']) {
                $cert_refid_to_update = (string)$cfgCert->refid;
                $import_log_message = 'updated';
                $cert_found = true;
                break;
            }
        }

        if ($cert_found == true) {
            // Update existing cert
            $node = $certModel->getNodeByReference('cert.' . $cert_refid_to_update);
            if ($node) {
                $node->crt = base64_encode($cert_content);
                $node->prv = base64_encode($key_content);
                $node->caref = $cert['caref'];
            }
        } else {
            // Create new cert
            $node = $certModel->cert->Add();
            $cert['refid'] = (string)$node->refid;
            $node->caref = $cert['caref'];
            $node->descr = $cert['descr'];
            $node->crt = base64_encode($cert_content);
            $node->prv = base64_encode($key_content);
        }

        $this->log("{$import_log_message} GoCertdist X.509 certificate: {$cert_cn} ({$cert['refid']})");

        $certModel->serializeToConfig(false, true);
        Config::getInstance()->save();

        /**
         * Step 3: Set as web UI certificate and restart
         */
        $config = Config::getInstance()->object();
        if (isset($config->system->webgui)) {
            $config->system->webgui->{'ssl-certref'} = $cert['refid'];
            Config::getInstance()->save();
            $this->log("Set web UI certificate to {$cert_cn} ({$cert['refid']})");

            $backend = new Backend();
            $backend->configdRun('webgui restart');
            $this->log("Web UI restarted.");
        }

        return true;
    }
}
