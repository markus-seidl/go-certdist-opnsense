<?php

@include_once('config.inc');
@include_once('util.inc');

use OPNsense\Core\Config;
use OPNsense\GoCertdist\GoCertdistCertificate;

syslog(LOG_NOTICE, "gocertdist: importcert.php called with: " . implode(' ', $_SERVER['argv']));

// Supported command line options
$options = getopt('', ['chain:', 'key:']);

if (isset($options['chain']) && isset($options['key'])) {
    syslog(LOG_NOTICE, "gocertdist: importcert.php parameters parsed successfully.");
    $importer = new GoCertdistCertificate();
    $importer->import($options['chain'], $options['key']);
} else {
    syslog(LOG_ERR, "gocertdist: importcert.php called with invalid parameters.");
    echo "Usage: importcert.php --chain <path_to_chain> --key <path_to_key>\n";
    exit(1);
}
