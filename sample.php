<?php
// オートローダー
function autoload($className)
{
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    require $fileName;
}


spl_autoload_register('autoload');

// ここからテスト
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
	$cache = new Polidog\Ultracache\Ultracache($config);
	$cache->set('test','cachetest');
	var_dump($cache->get('test'));

} catch (Polidog\Ultracache\Exception\NosupportDriverException $noe ) {
	echo $noe->getMessage();
	echo "\n";
}

