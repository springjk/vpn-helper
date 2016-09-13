<?php
namespace Springjk\System;

interface VpnInterface
{
    /**
     * Get VPN server list from system configuration file
     *
     * @return array VPN Server list
     */
    function getServers();

    /**
     * Ping VPN server list and insert ping avg to servers
     *
     * @param  array $servers VPN server list
     *
     * @return array Ping test result
     */
    function pingTest($servers);

    /**
     * Analysis VPN server list form result log
     *
     * @param array    $servers  VPN server list
     * @param callable $callable A PHP callback
     *
     * @return array VPN server list ping result
     */
    function analysisPingResult($servers, callable $callable);

    /**
     * Connection A VPN
     *
     * @param  array $vpn_connection_name A VPN server connection name
     *
     * @return bool  Connection status
     */
    function connection($vpn_connection_name);

    /**
     * Check vpn connection status
     *
     * @return bool Connection status
     */
    function checkConnectionStatus();
}
