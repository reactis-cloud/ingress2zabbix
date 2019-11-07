<?php
$ZABBIX_URL = 'https://zabbix/api_jsonrpc.php';
$ZABBIX_USER = '';
$ZABBIX_PASSWORD = '';
$ZABBIX_HOST_ID = 10000;

require_once 'vendor/autoload.php';
require_once 'vendor/confirm-it-solutions/php-zabbix-api/build/ZabbixApi.class.php';

$ingresses = shell_exec('kubectl get ingresses --all-namespaces -o=jsonpath=\'{.items[*].spec.rules[*].host}\' | tr " " "\n"');
$ingresses = explode("\n", $ingresses);

$api = new ZabbixApi\ZabbixApi($ZABBIX_URL, $ZABBIX_USER, $ZABBIX_PASSWORD);

foreach ($ingresses as $ingress) {
	try {
		$web = $api->httptestCreate([
			'name' => $ingress,
			'hostid' => $ZABBIX_HOST_ID,
			'steps' => [[
				'name' => "Homepage",
				'url' => "https://" . $ingress,
				'status_codes' => "200",
				"no" => 1
			]],
		]);
	}
	catch (Exception $e) {

	}
	try {
		$web = $api->triggerCreate([
			'description' => 'HTTP test failed: ' . $ingress,
			'expression' => '{Zabbix server:web.test.fail['.$ingress.'].last()}=1',
			'priority' => 4
		]);
	}
	catch (Exception $e) {

	}
}