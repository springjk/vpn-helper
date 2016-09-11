# VPN Helper
测试 VPN 线路列表并连接延迟最低的线路。
## 背景
很多 VPN 服务商提供了多个服务器供我们使用，但是他们大多数都没有提供测试工具。我们可以使用`ping`进行测速得到延迟最低的一个服务器，但遗憾的是很多情况下复杂的网络环境使服务器线路的延迟经常变化，这时使用`VPN Helper`可以快速的连接延迟最低的一个。

## 安装
``` bash
$ curl -sS https://raw.githubusercontent.com/springjk/vpn-speed-test/master/installer | php
```
## 使用
### 自动连接
**测速并连接**

``` shell
$ vpn run
```

**查看 VPN 列表**

``` shell
$ vpn run
```

**功能菜单**

``` shell
$ vpn

Vpn helper version v1.0.0 build 29d8b90

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  help     Displays help for a command
  list     Lists commands
  run      Ping test all vpn server and connection the fastest one
  servers  Displays local vpn server list
```
## 协议
MIT

