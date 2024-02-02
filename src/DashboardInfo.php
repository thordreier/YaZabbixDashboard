<?php
namespace YaZabbixDashboard;

class DashboardInfo {
    private $info;
    
    public function __construct($path, $dashboard, $token) {
        $yaml = yaml_parse_file($path);
        if (!$this->info = current(array_filter($yaml, function ($v) use ($dashboard, $token) {return $v['dashboard']==$dashboard && $v['token']==$token;}))) {
            throw new \Exception('Dashboard/token not found');
        }
    }

    public function get($property, $default=null) {
        if (isset($this->info[$property])) {
            return $this->info[$property];
        } elseif ($default == 'throw') {
            throw new \Exception("Property $property not found");
        } else {
            return $default;
        }
    }
}
