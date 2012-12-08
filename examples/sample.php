<?php
require __DIR__ . '/../vendor/autoload.php';

// 設定
$config = array(
	'remote'	=> array(
		'Memcached' => array(
			'servers' => array(
				array('localhost',11211),
			),
		),
	),
	'local'		=> array(
		'Apc' => array(), // 別に設定は特になし、でも空配列じゃないとだめだよー
	),
);

try { 
	$cache = new Ultracache\Ultracache($config);
	$cache->set('test','cachetest');

} catch (Ultracache\Exception\NosupportDriverException $noe ) {
	echo $noe->getMessage();
	echo "\n";
}

