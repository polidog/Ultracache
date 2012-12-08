
Ultracache
==========

ウルトラキャッシュはストレス発散とネタ的な意味をこめて、作ったPHPのライブラリです。
MemacheサーバとAPサーバが別サーバのときに、いちいちmemcacheからデータを取得するのって意外とネットワークの帯域を消費するんじゃないかと僕は思ったわけです。  
ローカルにキャッシュがあるときはローカルから取得して、無い場合はmemcaheから取得するみたいな感じの実装です。

一応Memcached以外にも対応できるようにドライバっぽく作ってあったりもします。

ちなみに、テストはしてないし、動作のチェックもしていません。
ただコードを書いただけなので、使う際は要注意してください。不具合があった場合はPull Request送ってもらえるとうれしいです。

Installation  
------------

    pecl install memcached
    pecl install apc


Configurations
--------------
	<?php
	$config = array(
		'remote'	=> array( // リモート側
			'Memcached' => array(
				'servers' => array(
					array('localhost',11211), // memcachedのサーバ設定とおなじ
				),
			),
		),
		'local'		=> array( // AP側(実行しているPHPの鯖)
			'Apc' => array(), // 別に設定は特になし、でも空配列じゃないとだめだよー
		),
	);

Usage
-----

一応PSR-0に従っているような気がするので適当にオートローダー用していただければ良いかと思います。
使い方は至って簡単。

	<?php
	require __DIR__ . '/../vendor/autoload.php';
	$config = array(
		'remote'	=> array( // リモート側
			'Memcached' => array(
				'servers' => array(
					array('localhost',11211), // memcachedのサーバ設定とおなじ
				),
			),
		),
		'local'		=> array( // AP側(実行しているPHPの鯖)
			'Apc' => array(), // 別に設定は特になし、でも空配列じゃないとだめだよー
		),
	);
	
	try { 
		$cache = new Ultracache\Ultracache($config);
		$cache->set('test','cachetest'); // キャッシュをセット
		var_dump($cache->get('test')); // キャッシュをゲット

	} catch (Ultracache\Exception\NosupportDriverException $noe ) {
		echo $noe->getMessage();
		echo "\n";
	}

たぶん、サンプルを見ればわかるかと思います。
キャッシュが無い場合はmemcachedとローカルのAPCキャッシュに保存して、キャッシュがローカルにある場合は、ローカルからのみキャッシュを取得して、ローカルに無い場合はmemcacheから取得して、ローカルのAPCにキャッシュを入れるような仕組みになっています。  
ほかのAPサーバとのキャッシュの同期とかはいれていないので、比較的変更の少ないデータをキャッシュすることをお勧めします。


