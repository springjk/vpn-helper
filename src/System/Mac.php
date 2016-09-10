<?php
namespace Springjk\System;

use CFPropertyList\CFPropertyList;

class Mac implements VpnInterface
{
    const LOG_PATH = '/tmp/vpn-speed-test/logs';

    public function getServers()
    {
        $servers = [];

        $plist = new CFPropertyList('/Library/Preferences/SystemConfiguration/preferences.plist');
        $plist = $plist->toArray();

        foreach ($plist['NetworkServices'] as $key => $value) {
            if (array_key_exists('PPP', $value)) {
                $servers[] = [
                    'name' => $value['UserDefinedName'],
                    'type' => $value['Interface']['SubType'],
                    'host' => $value['PPP']['CommRemoteAddress'],
                ];
            }
        }

        return $servers;
    }

    public function pingTest($servers)
    {
        $this->createLogPath();
        $this->clearLogPath();

        # background run ping and write to log file
        foreach ($servers as $key => $server) {
            exec('ping -c 5 ' . $server['host'] . ' > ' . self::LOG_PATH . '/' .  $key . '.log &');
        }
    }

    public function createLogPath()
    {
        if (!is_dir(self::LOG_PATH)) {
            try {
                mkdir(self::LOG_PATH, 0777, true);
            } catch (\Exception $e) {
                echo 'An error occurred while creating your directory at ' . self::LOG_PATH;
            }
        }
    }
    public function clearLogPath()
    {
        # clear history log
        $files = scandir(self::LOG_PATH);

        foreach ($files as $key => $file) {
            $file_path = self::LOG_PATH . '/' . $file;
            if (is_file($file_path)) {
                unlink($file_path);
            }
        }
    }

    public function analysisPingResult($servers, $call_back_function)
    {
        $servers_and_result = $servers;

        do {
            foreach ($servers as $key => $value) {
                $file_path = self::LOG_PATH . '/' . $key . '.log';

                $data = file($file_path);

                $last_line = array_pop($data);

                if (strpos($last_line, '100.0% packet loss') !== false) {
                    $servers_and_result[$key]['min'] = 'down';
                    $servers_and_result[$key]['max'] = 'down';
                    $servers_and_result[$key]['avg'] = 'down';
                } else if (strpos($last_line, 'avg')) {
                    preg_match_all('/[1-9]\d*\.\d{3}/', $last_line, $matches);

                    $servers_and_result[$key]['min'] = $matches[0][0];
                    $servers_and_result[$key]['max'] = $matches[0][2];
                    $servers_and_result[$key]['avg'] = $matches[0][1];
                } else {
                    // ping still running
                    continue;
                }

                unset($servers[$key]);

                $call_back_function();
            }
        } while (!empty($servers));

        return $servers_and_result;
    }

    public function connection($vpn_connection_name)
    {
        $script_code_template = [
            'tell application "System Events"',
            'tell current location of network preferences',
            'set VPNservice to service "' . $vpn_connection_name . '" -- name of the VPN service',
            'if exists VPNservice then connect VPNservice',
            'end tell',
            'end tell',
        ];
        $script_code = 'osascript';

        foreach ($script_code_template as $value) {
            $script_code .= ' -e \'' . $value . '\'';
        }

        exec($script_code);
    }

    public function checkConnectionStatus($vpn_connection_name)
    {
        exec('ifconfig |grep ppp0', $result);

        if ($result !== '') {
            return true;
        } else {
            return false;
        }
    }
}