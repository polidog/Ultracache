<?php

namespace Ultracache\Driver;

/**
 * MySQL用ドライバ
 * スキーマ構造はkey,valueでいいかね
 * @author polidog <polidogs@gmail.com>
 */
class Mysql extends AbstractDriver {

	/**
	 * @var \mysqli
	 */
	private $mysql = null;

	/**
	 * 使用するカラム名
	 * @var string 
	 */
	private $columns = array('key', 'value', 'expir');

	/**
	 * テーブル名
	 * @var string
	 */
	private $table = 'ultracache';
	private $database = 'ultracache';

	/**
	 * 初期化を行う
	 * @param array $config
	 * @throws NosupportDriverException
	 */
	protected function init(array $config) {

		if (!class_exists('mysqli')) {
			throw new NosupportDriverException('mysqli class not found');
		}

		$this->mysql = new \mysqli();

		foreach (array('host', 'user', 'password', 'database', 'port', 'socket') as $value) {
			if (!isset($config[$value])) {
				$config[$value] = null;
			}
		}

		// ホストの指定が無い場合
		if (empty($config['host'])) {
			$config['host'] = 'localhost';
		}

		// ユーザーの指定が無い
		if (empty($config['user'])) {
			$config['user'] = 'root';
		}

		// DBの指定が無い場合
		if (empty($config['database'])) {
			$config['database'] = $this->database;
		} else {
			$this->database = $config['database'];
		}

		// テーブル名が指定されてたら
		if (issset($config['table'])) {
			$this->table = $config['table'];
		}


		$this->mysql = new \mysqli($config['host'], $config['user'], $config['password'], $config['dataabse'], $config['port'], $config['socket']);
		if ($this->mysql->connect_error) {
			throw new NosupportDriverException('mysql connect error');
		}
		$this->isSupported = true;
		return true;
	}

	/**
	 * データを取得する
	 * @param string $key
	 * @return array|boolean
	 */
	public function get($key) {
		$data = $this->fetch($key);
		if (is_array($data)) {
			$data = array_pop($data);
			if (isset($data['value'])) {
				$data['value'] = unserialize($data['value']);
			} else {
				return false;
			}
		}
		return $data;
	}

	/**
	 * キャッシュをセットする
	 * @param string $key
	 * @param mixed $value
	 * @param int $expier
	 * @return boolean
	 */
	public function set($key, $value, $exper = null) {
		;
	}

	/**
	 * キャッシュを削除する
	 * @param string $key
	 * @return boolean
	 */		
	public function delete($key) {
		;
	}

	/**
	 * データを取得する
	 * @param int $key
	 * @return boolean
	 */
	private function fetch($key) {
		$colmunString = implode(', ', $this->columns);
		$stmt = $this->mysql->prepare('SELECT ' . $colmunString . ' FROM ' . $this->table . ' WHERE key = ? LIMIT 1');
		if (!$stmt) {
			return false;
		}

		if ($stmt->bind_param('s', $key)) {
			return false;
		}
		$data = $stmt->execute();
		$stmt->close();
		return $data;
	}

	/**
	 * デストラクタ
	 */
	public function __destruct() {
		if (is_object($this->mysql)) {
			$this->mysql->close();
		}
	}

}
