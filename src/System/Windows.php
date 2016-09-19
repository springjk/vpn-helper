<?php
namespace Springjk\System;

class Windows implements VpnInterface
{

    public $log_path;


    public function __construct()
    {
        $this->log_path = sys_get_temp_dir() . '\vpn-helper\logs';
    }

    public function getServers()
    {
        $servers = [];

        $i = 0;

        exec("powershell Get-VpnConnection", $output);

        foreach ($output as $key => $value) {
            $explode_array = explode(':', $value);

            if (count($explode_array === 2)) {

                switch (trim($explode_array[0])) {
                    case 'Name':
                        if (isset($servers[$i]['name'])) {
                            $i++;
                        }

                        $servers[$i]['name'] = trim($explode_array[1]);

                        break;
                    case 'TunnelType':
                        $type = trim($explode_array[1]);

                        $servers[$i]['type'] = $type === 'Automatic' ? 'PPTP' : strtoupper($type);

                        break;
                    case 'ServerAddress':
                        $servers[$i]['host'] = trim($explode_array[1]);

                        break;
                    default:
                        // do nothing
                        break;
                }
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
            exec('start /b ping -n 5 ' . $server['host'] . ' > ' . $this->log_path . '/' . $key . '.log');
        }
    }

    public function createLogPath()
    {
        if (!is_dir($this->log_path)) {
            try {
                mkdir($this->log_path, 0777, true);
            } catch (\Exception $e) {
                echo 'An error occurred while creating your directory at ' . $this->log_path;
            }
        }
    }

    public function clearLogPath()
    {
        # clear history log
        $files = scandir($this->log_path);

        foreach ($files as $key => $file) {
            $file_path = $this->log_path . '/' . $file;
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

                $file_path = $this->log_path . '\\' . $key . '.log';

                $data = file($file_path);

                $last_line = array_pop($data);

                $last_line = iconv('gbk', 'utf-8', $last_line);

                if (strpos($last_line, '100% 丢失') !== false) {
                    # ping lost
                    $servers_and_result[$key]['min'] = 'down';
                    $servers_and_result[$key]['max'] = 'down';
                    $servers_and_result[$key]['avg'] = 'down';
                } else if (strpos($last_line, '平均')) {
                    # ping success
                    $matches = explode('，', $last_line);

                    $servers_and_result[$key]['min'] = preg_replace('/\D/s', '', $matches[0]);
                    $servers_and_result[$key]['max'] = preg_replace('/\D/s', '', $matches[1]);
                    $servers_and_result[$key]['avg'] = preg_replace('/\D/s', '', $matches[2]);
                } else {
                    # ping still running
                    continue;
                }

                
                $finish_count++;

                $callable('running');

                unset($servers[$key]);
            }
        } while (!empty($servers));

        return $servers_and_result;
    }

    public function connection($vpn_connection_name)
    {
        $shell =  sprintf('scutil --nc start "%s"', $vpn_connection_name);

        exec($shell, $result, $result_code);

        if ($result_code === 0) {
            return true;
        } else {
            return false;
        }
    }

    public function checkConnectionStatus()
    {
        // scutil --nc status "connection name" | grep "ServerAddress"
        exec('ifconfig |grep ppp0', $result, $result_code);

        if (!empty($result) && $result_code === 0) {
            return true;
        } else {
            return false;
        }
    }
}