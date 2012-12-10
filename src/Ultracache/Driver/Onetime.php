<?php

namespace Ultracache\Driver;

/**
 * 1リクエストの間のみにデータが保持されるドライバ
 * @author polidog <polidogs@gmail.com>
 */
class Onetime extends AbstractDriver {

	private static $storage = null;
	private static $expers = array();

	protected function init(array $config) {
		if (!is_array(static::$storage)) {
			static::$storage = array();
		}
		$this->isSupported = true;
	}

	/**
	 * データを取得する
	 * @param string $key
	 * @return array|boolean
	 */
	public function get($key) {
		if (isset(static::$storage[$key])) {
			$limit = static::$expers[$key]['time'] + static::$expers[$key]['exper'];
			if (time() > $limit) {
				// 有効期限が過ぎている
				$this->delete($key);
				return false;
			}
			return static::$storage[$key];
		}
		return false;
	}

	/**
	 * キャッシュをセットする
	 * @param string $key
	 * @param mixed $value
	 * @param int $expier
	 * @return boolean
	 */
	public function set($key, $value, $exper = null) {
		static::$storage[$key] = $value;
		static::$expers[$key] = array(
			'time' => time(),
			'exper' => $exper,
		);
		return true;
	}

	/**
	 * 削除する
	 * @param string $key
	 */
	public function delete($key) {
		if (isset(static::$storage[$key])) {
			unset(static::$storage[$key]);
		}
		if (isset(static::$expers[$key])) {
			unset(static::$expers[$key]);
		}
		return true;
	}

}