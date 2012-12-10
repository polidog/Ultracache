<?php

namespace Ultracache\Driver;

/**
 * PHPSession用のドライバ
 * @author polidog <polidogs@gmail.com>
 */
class PhpSession extends AbstractDriver {

	protected function init(array $config) {
		session_start();
		session_regenerate_id(true);
	}

	/**
	 * キャッシュを取得する
	 * @param string $key
	 * @return array
	 */
	public function get($key) {
		if (isset($_SESSION[$key]['data'])) {
			if ($_SESSION[$key]['limit'] <= time()) {
				// 有効期限切れ
				unset($_SESSION[$key]);
			} else {
				return $_SESSION[$key]['data'];
			}
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
		$time = time();
		$exper = (int) $exper;
		$time += $exper;
		$_SESSION[$key] = array('data' => $value, 'limit' => $time);
		return true;
	}

	/**
	 * キャッシュを削除する
	 * @param string $key
	 * @return boolean
	 */
	public function delete($key) {
		if (isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
		}
		return true;
	}

}