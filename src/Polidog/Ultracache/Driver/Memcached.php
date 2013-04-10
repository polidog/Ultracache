<?php
namespace Polidog\Ultracache\Driver;

/**
 * memcachedサポートのクラス
 * @author polidog <polidog@gmail.com>
 * @version 0.1
 */
class Memcached extends AbstractDriver
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
	
	/**
	 * キャッシュを取得する
	 * @param string $key
	 * @return array
	 */
	public function get($key) {
		return $this->Memcached->get($key);
	}
	
	/**
	 * キャッシュをセットする
	 * @param string $key
	 * @param mixed $value
	 * @param int $expier
	 * @return boolean
	 */
	public function set($key, $value, $exper = null) {
		return $this->Memcached->set($key,$value,$exper);
	}
	
	/**
	 * キャッシュを削除する
	 * @param string $key
	 * @return boolean
	 */
	public function delete($key) {
		return $this->Memcached->delete($key);
	}
}
