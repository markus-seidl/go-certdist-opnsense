<?php

namespace OPNsense\GoCertdist\Api;

use OPNsense\Base\ApiControllerBase;

class LogfileController extends ApiControllerBase
{
    public function searchAction()
    {
        $this->sessionClose();
        $logFile = '/var/log/certdist.log';

        $response = [
            'current' => 1,
            'rowCount' => 10, // Default
            'rows' => [],
            'total' => 0
        ];

        if ($this->request->isPost()) {
            $response['current'] = (int)$this->request->getPost('current', 'int', 1);
            $response['rowCount'] = (int)$this->request->getPost('rowCount', 'int', 10);
            $searchPhrase = $this->request->getPost('searchPhrase', 'string', '');
        }

        if (file_exists($logFile) && is_readable($logFile)) {
            $log_data = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $filtered_data = [];

            foreach ($log_data as $line) {
                // Filter by search phrase if provided
                if ($searchPhrase === '' || stripos($line, $searchPhrase) !== false) {
                    preg_match('/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z):\s(.*)$/', $line, $matches);
                    if (count($matches) == 3) {
                        $filtered_data[] = ['timestamp' => $matches[1], 'message' => $matches[2]];
                    } else {
                        $filtered_data[] = ['timestamp' => '', 'message' => $line];
                    }
                }
            }

            $response['total'] = count($filtered_data);

            // Reverse sort to show latest entries first
            $filtered_data = array_reverse($filtered_data);

            // Paginate
            $offset = ($response['current'] - 1) * $response['rowCount'];
            $response['rows'] = array_slice($filtered_data, $offset, $response['rowCount']);
        }

        return $response;
    }
}
