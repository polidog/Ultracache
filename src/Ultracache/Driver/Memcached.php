<?php
namespace Ultracache\Driver;

/**
 * memcachedサポートのクラス
 * @author polidog <polidog@gmail.com>
 * @version 0.1
 */
class Memcached extends Driver
{
	
	private $Memcached;
	
	
	protected $isSupported = false;
	
	/**
	 * 初期処理
	 * @param array $config
	 */
	protected function init(array $config) {
		if (!class_exists('\Memcached') ) {
			// memcachedに対応していない場合
			$this->isSupported = false;
			return false;
		}

		
		if ( !isset($config['servers']) ) {
			return false;
		}
		
		$this->Memcached = new \Memcached();
		$this->Memcached->addServers($config['servers']);
		
		$this->isSupported = true;
		return true;
	}
	
	
	public function get($key) {
		return $this->Memcached->get($key);
	}
	
	public function set($key, $value, $exper = null) {
		return $this->Memcached->set($key,$value,$exper);
	}
	
	public function delete($key) {
		return $this->Memcached->delete($key);
	}
}
