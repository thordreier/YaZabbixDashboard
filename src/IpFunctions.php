<?php
namespace YaZabbixDashboard;

class IpFunctions {
    public static function ipToLong ($ip) {
        $ip_long = ip2long($ip);
        if (! is_numeric($ip_long)) {
            throw new \Exception('IP is not valid: '.$ip);
        }
        return $ip_long;
    }

    public static function maskToLong ($mask) {
        if (! is_numeric($mask) || $mask < 0 || $mask > 32) {
            throw new \Exception('Mask is not valid: '.$mask);
        }
        return ~((1 << (32 - $mask)) - 1) & 0xFFFFFFFF;
    }

    public static function getNetLong ($ip_long, $mask_long) {
        return $ip_long & $mask_long;
    }

    public static function splitCidr ($cidr) {
        $e = explode('/', $cidr, 2);
        $ip = $e[0];
        $mask = isset($e[1]) ? $e[1] : 32;
        $ip_long = IpFunctions::ipToLong($ip);
        $mask_long = IpFunctions::maskToLong($mask);
        $net_long = IpFunctions::getNetLong($ip_long, $mask_long);
        $net = long2ip($net_long);
        return [$ip, $mask, $net, $ip_long, $mask_long, $net_long];
    }

    public static function ipInCidr ($ip, $cidr) {
        $x = IpFunctions::splitCidr($cidr);
        list(,,,, $mask_long, $net_long) = IpFunctions::splitCidr($cidr);
        $ip_long = IpFunctions::ipToLong($ip);
        return IpFunctions::getNetLong($ip_long, $mask_long) == $net_long;
    }

    public static function ipInCidrs ($ip, $cidrs) {
        foreach ($cidrs as $cidr) {
            if (IpFunctions::ipInCidr($ip, $cidr)) {
                return true;
            }
        }
        return false;
    }

}
