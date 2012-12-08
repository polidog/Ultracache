<?php

namespace Polidog\Ultracache;

/**
 * ウルトラキャッシュ
 * サーバ側とローカル側の両方でキャッシュを持たせるようにする
 * @author polidog 
 */
class Ultracache {

	/**
	 * Server側のキャッシュライブラリ
	 * @var Ultracache\Cache\Server
	 */
	protected $Server;

	/**
	 * ローカル側のキャッシュ来bら裏
	 * @var Ultracache\Cache\Local\Local
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
		$this->Server = $this->createDriver($config, 'server');
		$this->Local = $this->createDriver($config, 'local');
	}

	/**
	 * キャッシュを取得する
	 * @param int $key キャッシュのキー
	 * @param boolean $saveLocalCache ローカル側に記録するかどうかのキャッシュ
	 * @return type
	 */
	public function get($key, $isServerOnlay = false) {

		if ($isServerOnlay) {
			// サーバーからのみ取得する場合
			return $this->Server->get($key);
		}

		$data = $this->Local->get($key);
		if (!$data) {
			// データが取得できない場合は別サーバから取得する
			$data = $this->Server->get($key);
			if (!$data) {
				// サーバからデータが取れない場合はローカルのデータを消す
				$this->Local->delete($key);
				return false;
			}

			$expier = $this->expire;

			// 作成日をから計算して有効期限を設定する
			if (isset($data['limit_time'])) {
				// 残りの有効期限を計算する
				$expier = time() - $data['limit_time'];
				if ($expier < 0) {
					// 有効期限切れ
					return false;
				}

				$this->Local->set($key, $data, $expier);
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
		$ret = $this->Server->set($key, $data, $expr);
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
		$this->Server->delete($key);
		$this->Local->delete($key);
	}

	/**
	 * サーバー側のキャッシュと同期させる
	 * @param int $key
	 * @return boolean
	 */
	public function sync($key) {
		$data = $this->Server->get($key);
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
					$this->Server->set($key, $data);
				}
				return true;
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

		$allClassName = '\\Polidog\\Ultracache\\Driver\\' . $className;
		$object = new $allClassName($config[$type][$className]);
		if ($object->isSuppoeted()) {
			return $object;
		}
		return false;
	}

}