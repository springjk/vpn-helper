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
            exec('ping -c 5 ' . $server['host'] . ' > ' . self::LOG_PATH . '/' . $key . '.log &');
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

    public function analysisPingResult($servers, callable $callable)
    {
        $servers_and_result = $servers;
        $finish_count = 0;

        do {
            foreach ($servers as $key => $server) {

                $file_path = self::LOG_PATH . '/' . $key . '.log';

                $data = file($file_path);

                $last_line = array_pop($data);

                if (strpos($last_line, '100.0% packet loss') !== false) {
                    # ping lost
                    $servers_and_result[$key]['min'] = 'down';
                    $servers_and_result[$key]['max'] = 'down';
                    $servers_and_result[$key]['avg'] = 'down';
                } else if (strpos($last_line, 'avg')) {
                    # ping success
                    preg_match_all('/[1-9]\d*\.\d{3}/', $last_line, $matches);

                    $servers_and_result[$key]['min'] = $matches[0][0];
                    $servers_and_result[$key]['max'] = $matches[0][2];
                    $servers_and_result[$key]['avg'] = $matches[0][1];
                } else {
                    # ping still running
                    continue;
                }

                $finish_count++;

                if (count($servers) == 1) {
                    $message = 'all finish';
                } else {
                    # get next key of server
                    $server_keys = array_keys($servers);
                    $this_server_key = array_search($key, $server_keys);
                    $next_server_key = (++$this_server_key == count($server_keys)) ? $server_keys[0] : $server_keys[$this_server_key];

                    $message = 'waiting result for ' . $servers[$next_server_key]['name'];
                }

                $callable($message);

                unset($servers[$key]);
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

        exec($script_code, $result, $result_code);

        if (!empty($result) && $result_code === 0) {
            return true;
        } else {
            return false;
        }
    }

    public function checkConnectionStatus()
    {
        exec('ifconfig |grep ppp0', $result, $result_code);

        if (!empty($result) && $result_code === 0) {
            return true;
        } else {
            return false;
        }
    }
}