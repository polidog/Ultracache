<?php

namespace Polidog\Ultracache;

use Polidog\Ultracache\Driver;
use Polidog\Ultracache\Exception;

/**
 * ウルトラキャッシュ
 * サーバ側とローカル側の両方でキャッシュを持たせるようにする
 * @author polidog 
 */
class Ultracache {

	/**
	 * Server側のキャッシュライブラリ
	 * @var Polidog\Ultracache\Driver\Driver
	 */
	protected $Remote;

	/**
	 * ローカル側のキャッシュ来bら裏
	 * @var Polidog\Ultracache\Driver\Driver
	 */
	protected $Local;

	/**
	 * 有効期限 秒数(デフォルトでは30日間)
	 * @var int 
	 */
	protected $expire = 2592000;

	/**
	 * 1時間同期されてなかったら、キャッシュを同期する
	 * @var string 
	 */
	protected $syncTrigerTime = 3600;

	public function __construct(array $config) {

		// ドライバの選択
		$this->Remote = $this->createDriver($config, 'remote');
		$this->Local = $this->createDriver($config, 'local');
	}

	/**
	 * キャッシュを取得する
	 * @param int $key キャッシュのキー
	 * @param boolean $isServerOnlay サーバ側からのみデータを強制的に取得するフラグ
	 * @return type
	 */
	public function get($key, $isServerOnlay = false) {

		if ($isServerOnlay) {
			// サーバーからのみ取得する場合
			return $this->Remote->get($key);
		}
		$time = time();
		$data = $this->Local->get($key);
		if (!$data) {
			// データが取得できない場合は別サーバから取得する
			$data = $this->Remote->get($key);
			if (!$data) {
				// サーバからデータが取れない場合はローカルのデータを消す
				$this->Local->delete($key);
				return false;
			}

			// 作成日をから計算して有効期限を設定する
			if (isset($data['limit_time'])) {
				// 残りの有効期限を計算する
				$expier = $time - $data['limit_time'];
				if ($expier < 0) {
					// 有効期限切れ
					return false;
				}
				$data['sync_type'] = $time;
				$this->Local->set($key, $data, $expier);
			}
		}
		else {
			// sync timeが超えてたらsyncする
			$checkSyncType = $time + $this->syncTrigerTime;
			if ( $data['sync_time'] >= $checkSyncType ) {
				// チェック期限がすぎてたら
				$data = $this->sync($key);
			}
		}
		
		return $data['store'];
	}

	/**
	 * データをセットする
	 * @param int $key
	 * @param mixed $value
	 * @param int $expr
	 * @param boolean $isServerOnly
	 * @return type
	 */
	public function set($key, $value, $expr = null, $isServerOnly = false) {
		$data['store'] = $value;
		$data['limit_time'] = time() * $expr;
		$data['sync_time'] = time();
		$ret = $this->Remote->set($key, $data, $expr);
		if ($ret && $isServerOnly == false) {
			$ret = $this->Local->set($key, $data, $expr);
		}
		return $ret;
	}

	/**
	 * 削除する
	 * @param type $key
	 */
	public function delete($key) {
		$this->Remote->delete($key);
		$this->Local->delete($key);
	}

	/**
	 * サーバー側のキャッシュと同期させる
	 * @param int $key
	 * @return array|boolean
	 */
	public function sync($key) {
		$data = $this->Remote->get($key);
		if (!$data) {
			$this->Local->delete($key);
			return true;
		} else {
			if (isset($data['limit_time'])) {
				$expier = time() - $data['limit_time'];
				if ($expier < 0) {
					// 期限切れのため削除
					$this->Local->delete($key);
				} else {
					// ローカル側のキャッシュを更新
					$data['sync_time'] = time();
					$this->Local->set($key, $data);

					// synctimeの更新
					$this->Remote->set($key, $data);
				}
				return $data;
			}
		}
		return false;
	}

	/**
	 * ドライバーを作成する
	 * @param array $config
	 * @param string $type
	 * @return \Ultracache\Core\Driver|boolean
	 */
	private function createDriver($config, $type) {

		if (!isset($config[$type]) || empty($config[$type])) {
			return false;
		}
		reset($config[$type]);
		
		$className = key($config[$type]);
		if ( $className == 'Driver' ) {
			throw new Exception\NosupportDriverException('driver is no suppot');
		}
		
		$c = __NAMESPACE__.'\\Driver\\'.$className;
		$object = new $c($config[$type][$className]);
		if ($object->isSuppoeted()) {
			return $object;
		}
		return false;
	}

}