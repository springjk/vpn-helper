<?php
namespace Springjk\System;

interface VpnInterface
{

    /**
     * get VPN server list from local pc config file
     *
     * @return array VPN Server list
     */
    function getServers();

    /**
     * ping VPN server list and insert ping avg to servers
     *
     * @param  array   $servers       VPN server list
     *
     * @return array                  ping result
     */
    function pingTest($servers);

    function analysisPingResult($servers, $call_back_function);

    /**
     * connection VPN
     *
     * @param  array $vpn_connection_name a VPN server connection name
     */
    function connection($vpn_connection_name);

    /**
     * check vpn connection status
     *
     * @param  string $vpn_connection_name vpn_connection_name
     *
     * @return bool                        connection status
     */
    function checkConnectionStatus($vpn_connection_name);
}
