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
        }

        // var_dump($result);

        // C:\Users\jiangkun\AppData\Roaming\Microsoft\Network\Connections\Pbk
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

        // windows
        // http://superuser.com/questions/950257/list-vpn-connections-and-properties-in-command-prompt

        // http://blog.affirmix.com/2011/01/12/how-to-configure-a-vpn-in-mac-os-x-usingapplescript/

        // netstat -i 检查 ppp0 判断连接状态
    }

    /**
     * get VPN server list from local pc config file
     *
     * @return array VPN Server list
     */
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

    /**
     * ping VPN server list and insert ping avg to servers
     *
     * @param  array   $servers       VPN server list
     * @param  boolean $show_progress show ping progress
     *
     * @return array                  ping result
     */
    private function pingTest($servers, $show_progress = false)
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
            exec('ping -c 5 ' . $server['host'] . ' > ' . $dir . $key . '.log &');
        }

        $avgs = [];

        if ($show_progress) {
            $progress = $this->cli->br()->progress()->total(count($servers));
        }

        $servers_temp = $servers;

        while (true) {
            foreach ($servers_temp as $key => $server) {

                $file_name = $key . '.log';

                $data = file($dir . $file_name);

                $last_line = array_pop($data);

                if (strpos($last_line, 'avg')) {
                    # for MAC OS
                    preg_match_all('/[1-9]\d*\.\d{3}/', $last_line, $matches);

                    $avg = $matches[0][1];

                    $avgs[$key] = $avg;

                    $servers[$key]['avg'] = $avg;
                } elseif (strpos($last_line, '100.0% packet loss')) {
                    $avgs[$key] = 'down';

                    $servers[$key]['avg'] = 'down';
                } else {
                    usleep(5000);
                    continue;
                }

                unset($servers_temp[$key]);

                if ($show_progress) {
                    $progress->current(count($avgs));
                }
            }

            if (count($avgs) === count($servers)) {
                break;
            }
        }

        return $servers;
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
            ['name', 'type', 'host', 'avg'],
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

}

$vpn = new VPN();

$vpn->run();
