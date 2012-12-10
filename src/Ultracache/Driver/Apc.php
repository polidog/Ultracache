<?php

namespace Ultracache\Driver;

/**
 * APCキャッシュ用ドライバ
 * @author polidog <polidogs@gmail.com>
 * @version 0.1
 */
class Apc extends AbstractDriver {

	/**
	 * 初期処理
	 * @param array $config
	 */
	protected function init(array $config) {
		if (!function_exists('apc_store')) {
			// APCサポートされていない場合
			$this->isSupported = false;
			return false;
		}

		$this->isSupported = true;
		return true;
	}

	/**
	 * キャッシュを取得する
	 * @param string $key
	 * @return array
	 */
	public function get($key) {
		return apc_fetch($key);
	}

	/**
	 * キャッシュをセットする
	 * @param string $key
	 * @param mixed $value
	 * @param int $expier
	 * @return boolean
	 */
	public function set($key, $value, $expier = null) {
		return apc_store($key, $value, $expier);
	}

	/**
	 * キャッシュを削除する
	 * @param string $key
	 * @return boolean
	 */
	public function delete($key) {
		return apc_delete($key);
	}

}