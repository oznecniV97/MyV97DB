<?php
/*username and password to access to db*/
$username = "root";
$password = "password";
/*IP Whitelist (not mandatory)*/
$clients = ["127.0.0.1", "3.224.*"];
/*SSH Part (not mandatory, require sshpass installed)*/
$useSshTunnel = true;
$sshUsername = "user";
$sshPassword = "password";
$sshRemoteIp = "127.0.0.1";
$sshRemotePort = "3306";
$sshLocalPort = "7001";
$sshTimeout = "20";