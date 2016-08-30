<?php
require __DIR__ . '/vendor/autoload.php';

use CFPropertyList\CFPropertyList;
use League\CLImate\CLImate;

class VPN
{
    /**
     * cli helper
     *
     * @var mixed
     */
    private $cli;

    public function __construct()
    {
        $this->cli = new CLImate;
    }

    /**
     * auto workflow
     */
    public function run()
    {

        $this->cli->out('读取 VPN 列表…');

        $servers = $this->getServers();

        if (empty($servers)) {
            $this->cli->br()->error('读取 VPN 列表失败');

            exit();
        } else {
            $this->cli->br()->out('ping 测速…');

            $servers = $this->pingTest($servers, true);

            $servers = $this->sortServers($servers);

            $this->outputColumns($servers);

            $fastest_vpn_name = $servers[0]['name'];
            $fastest_vpn_info = '延迟最低的线路为：' . $fastest_vpn_name . ' AVG: ' . $servers[0]['avg'];

            $this->cli->br()->lightBlue($fastest_vpn_info);

            $this->cli->br()->out('正在连接' . $fastest_vpn_name . '…');

            $this->connection($fastest_vpn_name);

            do {
                usleep(200);

                $status = $this->checkConnectionStatus($fastest_vpn_name);
            } while (!$status);

            $this->cli->br()->info('success,enjoy!');
        }

        // var_dump($result);

        // %appdata%\Microsoft\Network\Connections\Pbk
        // Get-VpnConnection
        // 测速
        // download google file
        // windows
        // rasdial "你的VPN Name" Username Password
        // exec("powershell C:\\Inetpub\\wwwroot\\emsrDev\\manual_shell.ps1", $output);
        // /Library/Preferences/SystemConfiguration/preferences.plist
        // networksetup listallnetworkservices
        // add art title
        //  查看所有网络连接列表
        // networksetup listallnetworkservices
        // exec("powershell Get-VpnConnection", $output);
        // windows
        // http://superuser.com/questions/950257/list-vpn-connections-and-properties-in-command-prompt

        // http://blog.affirmix.com/2011/01/12/how-to-configure-a-vpn-in-mac-os-x-usingapplescript/

        // netstat -i 检查 ppp0 判断连接状态
        //
        // https://technet.microsoft.com/en-us/library/jj613766(v=ws.11).aspx
    }

    /**
     * get VPN server list from local pc config file
     *
     * @return array VPN Server list
     */
    public function getServers()
    {
        $os_type = $this->getOSType();

        switch ($os_type) {
            case 'linux':
                // to do
                break;
            case 'macOS':
                $servers = $this->getServersFromMacOS();
                break;
            case 'windows':
                $servers = $this->getServersFromWindows();
                break;
            default:
                $servers = [];
                break;
        }

        return $servers;
    }

    /**
     * get VPN server list from local pc config file from macOS
     *
     * @return array VPN Server list
     */
    private function getServersFromMacOS()
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

    /**
     * get VPN server list from local pc config file from windows
     *
     * @return array VPN Server list
     */
    private function getServersFromWindows()
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

    /**
     * ping VPN server list and insert ping avg to servers
     *
     * @param  array   $servers       VPN server list
     * @param  boolean $show_progress show ping progress
     *
     * @return array                  ping result
     */
    public function pingTest($servers, $show_progress = false)
    {
        $dir = __DIR__ . '/logs/';

        if (!is_dir($dir)) {
            mkdir($dir);
        } else {
            # 清理上次的纪录
            $files = scandir($dir);

            foreach ($files as $key => $file) {
                $file_path = $dir . $file;
                if (is_file($file_path)) {
                    unlink($file_path);
                }
            }
        }

        # 后台运行 ping 并将结果返回至 log 文件中
        foreach ($servers as $key => $server) {
            $os_type = $this->getOSType();

            switch ($os_type) {
                case 'macOS':
                    exec('ping -c 5 ' . $server['host'] . ' > ' . $dir . $key . '.log &');
                    break;
                case 'windows':
                    exec('start /b ping -n 5 ' . $server['host'] . ' > ' . $dir . $key . '.log');
                    break;
                default:
                    # code...
                    break;
            }

        }

        $i = 0;

        if ($show_progress) {
            $progress = $this->cli->br()->progress()->total(count($servers));
        }

        $servers_temp = $servers;

        while (true) {
            foreach ($servers_temp as $key => $server) {

                $file_path = $dir . $key . '.log';

                $result = $this->analysisPingResult($file_path);

                if ($result != false) {
                    $i++;
                    $servers[$key] = array_merge($servers[$key], $result);
                } else {
                    usleep(5000);
                    continue;
                }

                unset($servers_temp[$key]);

                if ($show_progress) {
                    $progress->current($i);
                }
            }

            if ($i === count($servers)) {
                break;
            }
        }

        return $servers;
    }

    private function analysisPingResult($file_path)
    {
        $data = file($file_path);

        $last_line = array_pop($data);

        if (strpos($last_line, '100.0% packet loss') || strpos($last_line, '100% 丢失')) {
            $result = [
                'min' => 'down',
                'max' => 'down',
                'avg' => 'down',
            ];

            return $result;
        } else if ((strpos($last_line, 'avg')) || (strpos($last_line, '平均'))) {

            $os_type = $this->getOSType();

            switch ($os_type) {
                case 'linux':
                    // todo
                    break;
                case 'macOS':
                    preg_match_all('/[1-9]\d*\.\d{3}/', $last_line, $matches);

                    $result = [
                        'min' => $matches[0][0],
                        'max' => $matches[0][2],
                        'avg' => $matches[0][1],
                    ];

                    break;
                case 'windows':
                    $matches = explode('，', $last_line);

                    $result = [
                        'min' => preg_replace('/\D/s', '', $matches[0]),
                        'max' => preg_replace('/\D/s', '', $matches[1]),
                        'avg' => preg_replace('/\D/s', '', $matches[2]),
                    ];

                    break;

                default:
                    $result =  false;
                    break;
            }
        } else {
            // ping still running
            $result = false;
        }

        return $result;
    }

    /**
     * out put ping result in columns
     *
     * @param  array $servers VPN server list
     */
    public function outputColumns($servers)
    {
        # add th
        $table = [
            ['name', 'type', 'host', 'min', 'max', 'avg'],
        ];

        foreach ($servers as $key => $value) {
            $table[] = array_values($value);
        }

        $this->cli->clear();

        $this->cli->br()->columns($table);
    }

    /**
     * sort VPN server list by avg
     *
     * @param  array $servers VPN server list
     *
     * @return array          VPN server list
     */
    public function sortServers($servers)
    {
        $sort_avg = [];

        foreach ($servers as $key => $value) {
            $sort_avg[] = $value['avg'];
        }

        array_multisort($sort_avg, SORT_ASC, $servers);

        return $servers;
    }

    /**
     * connection VPN
     *
     * @param  array $vpn_connection_name a VPN server connection name
     */
    public function connection($vpn_connection_name)
    {
        $script_code_templete = [
            'tell application "System Events"',
            'tell current location of network preferences',
            'set VPNservice to service "' . $vpn_connection_name . '" -- name of the VPN service',
            'if exists VPNservice then connect VPNservice',
            'end tell',
            'end tell',
        ];
        $script_code = 'osascript';

        foreach ($script_code_templete as $key => $value) {
            $script_code .= ' -e \'' . $value . '\'';
        }

        exec($script_code);
    }

    /**
     * check vpn connection status
     *
     * @param  string $vpn_connection_name vpn_connection_name
     *
     * @return bool                        connection status
     */
    public function checkConnectionStatus($vpn_connection_name)
    {
        $os_type = $this->getOSType();

        switch ($os_type) {
            case 'linux':
                // todo
                break;
            case 'macOS':
                exec('ifconfig |grep ppp0', $result);
                break;
            case 'windows':
                exec('ipconfig |find /i "' . $vpn_connection_name . '"', $result);
                break;

            default:
                $result = '';
                break;
        }

        if ($result !== '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get php server OS type
     *
     * @return string OS type
     */
    public function getOSType()
    {
        switch (PHP_OS) {
            case 'Linux':
                $os_type = 'linux';

                break;
            case 'Darwin':
                $os_type = 'macOS';

                break;
            case 'WIN32':
            case 'WINNT':
            case 'Windows':
                $os_type = 'windows';

                break;

            default:
                $os_type = 'others';

                break;
        }

        return $os_type;
    }
}

$vpn = new VPN();

$vpn->run();
