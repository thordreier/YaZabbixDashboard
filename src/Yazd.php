<?php
namespace YaZabbixDashboard;

use Psr\Http\Message\ServerRequestInterface as Request;

class Yazd {
    private $dashboard;
    private $request;
    private $dashboardinfo;
    private $zabbixapi;

    public function __construct(String $dashboard, String $token, Request $request) {
        $this->dashboard = $dashboard;
        $this->request = $request;
        $this->dashboardinfo = new DashboardInfo(DASHBOARDSSYAML, $dashboard, $token);
        $this->zabbixapi = new ZabbixApi(ZABBIXURL, $this->dashboardinfo->get('zabbixtoken'));
        $this->checkAccess();
    }

    public function checkAccess () {
        if ($cidrs = $this->dashboardinfo->get('ipv4_whitelist', false)) {
            $ip = $this->request->getServerParams()['REMOTE_ADDR'];
            if (! IpFunctions::ipInCidrs($ip, $cidrs)) {
                throw new \Exception("IP address is not whitelisted: ".$ip);
            }
        }
    }

    public function getHostGroups () {
        $zabbixhostgroups = $this->zabbixapi->request('hostgroup.get', [
            'selectHosts' => 'extend',
        ]);
        $hostgroups = array_map(function ($zabbixhostgroup) {
            $hosts = array_map(function ($zabbixhost) {
                $name = $zabbixhost['name'];
                foreach ($this->dashboardinfo->get('hostname_replace', []) as $r) {
                    $name = preg_replace('/'.$r['pattern'].'/', $r['replacement'], $name);
                }
                return [
                    'id' => $zabbixhost['hostid'],
                    'name' => $name,
                    'maintenance' => $zabbixhost['maintenance_status'],
                ];
            }, $zabbixhostgroup['hosts']);
            array_multisort(array_column($hosts, 'name'), $hosts);
            return [
                'id' => $zabbixhostgroup['groupid'],
                'name' => $zabbixhostgroup['name'],
                'hosts' => $hosts,
            ];
        }, $zabbixhostgroups);
        array_multisort(array_column($hostgroups, 'name'), $hostgroups);
        return $hostgroups;
    }

    public function getProblems () {
        $zabbixproblems_params = [
            'selectTags' => 'extend',
            'suppressed' => false,
            'min_severity' => 4,
            'severities' => $this->dashboardinfo->get('severities', [2,3,4,5]),
            'object' => 0,  # 0=trigger, 4=item, 5=LLD rule
        ];
        if (! $this->dashboardinfo->get('show_acknowledged', false)) {
            $zabbixproblems_params['acknowledged'] = false;
        }
        $zabbixproblems = $this->zabbixapi->request('problem.get', $zabbixproblems_params);
        $zabbixtriggers = $this->zabbixapi->request('trigger.get', [
            'skipDependent' => 1,
            'triggerids' => array_column($zabbixproblems, 'objectid'),
            'selectHosts' => 'shorten',
            'output' => [
                'triggerid',
                'hosts',
                'status',
            ],
            'filter' => [
                'status' => 0  # 0=enabled, 1=disabled
            ],
        ]);
        $zabbixproblems_objectid = array_column($zabbixproblems, null, 'objectid');
        $hostgroups = array_map(function ($zabbixtrigger) use ($zabbixproblems_objectid) {
            $zabbixproblem = $zabbixproblems_objectid[$zabbixtrigger['triggerid']];
            return [
                'id' => $zabbixproblem['eventid'],
                'hostid' => $zabbixtrigger['hosts'][0]['hostid'],
                'severity' => $zabbixproblem['severity'],
                'name' => $zabbixproblem['name'],
                'acknowledged' => $zabbixproblem['acknowledged'],
            ];
        }, $zabbixtriggers);
        return $hostgroups;
    }

    public function getHostsWithProblems () {
        $problems = $this->getProblems();
        $hosts_id = [];
        foreach ($problems as $problem) {
            $hostid = $problem['hostid'];
            unset($problem['hostid']);
            $hosts_id[$hostid]['hostid'] = $hostid;
            $hosts_id[$hostid]['severity'] = max(isset($hosts_id[$hostid]['severity']) ? $hosts_id[$hostid]['severity'] : 0, $problem['severity']);
            $hosts_id[$hostid]['problems'][] = $problem;
        }
        return array_values($hosts_id);
    }

    public function formatTime($timezone, $format) {
        $default = date_default_timezone_get();
        date_default_timezone_set($timezone);
        $time = date($format, time());
        date_default_timezone_set($default);
        return $time;
    }

    public function getClocks () {
        return array_map(function ($c) {
            return [
                'name' => $c['name'],
                'time' => $this->formatTime($c['timezone'], $c['format']),
            ];
        }, $this->dashboardinfo->get('clocks', [
            [
                'name' => date_default_timezone_get(),
                'timezone' => date_default_timezone_get(),
                'format' => 'H:i',
            ],
        ]));
    }
}
