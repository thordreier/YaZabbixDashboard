# YaZabbixDashboard

Yet Another Zabbix Dashboard - Written in PHP.

## Install

Make sure Composer is installed - [check this link](https://getcomposer.org/download/)

```
cd /opt/
git clone https://github.com/thordreier/YaZabbixDashboard.git

cd YaZabbixDashboard
composer install

cp contrib/nginx.conf /etc/nginx/sites-available/yazabbixdashboard
editor /etc/nginx/sites-available/yazabbixdashboard
ln -s /etc/nginx/sites-available/yazabbixdashboard /etc/nginx/sites-enabled/
systemctl reload nginx
```

## Setup

```
cp settings.example.php settings.php
editor settings.php

cp dashboards.example.yaml dashboards.yaml
editor dashboards.yaml
```
