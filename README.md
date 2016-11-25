# VPN Helper

README: [English](https://github.com/springjk/vpn-helper/blob/master/README.md) | [中文](https://github.com/springjk/vpn-helper/blob/master/README-zh.md)

![](http://oac57xnsh.bkt.clouddn.com/vpn-helper.png)

Test VPN servers and connection the fastest one.

## Background

In most cases VPN providers provide us multiple servers, but they don't provide a speed test tool to get the fastest of servers. Even if we can use `ping` to test, regrettably the complex network environment in many cases make the server constantly changing line delay, Then use `VPN Helper` can quickly connect the fastest one.

## Requirement

* PHP >= 5.6

## Installation

``` bash
$ curl -sS https://raw.githubusercontent.com/springjk/vpn-helper/master/installer | php
```

## Usage

**Test and connection**

``` shell
$ vpn run
```

**Display VPN servers**

``` shell
$ vpn servers
```

**Lists commands**

``` shell
$ vpn

Vpn helper version v1.2.0 build b5b6469

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages

Available commands:
  connection  Connection a vpn server by connection name
  help        Displays help for a command
  list        Lists commands
  ping        Ping test all vpn servers and display result
  run         Ping test all vpn servers and connection the fastest one
  servers     Displays all vpn servers
```

## License

MIT

