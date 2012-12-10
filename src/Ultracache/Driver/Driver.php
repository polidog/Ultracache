<?php

namespace Ultracache\Driver;
use Ultracache\Exception;

/**
 * キャッシュ用ドライバーの抽象化クラス
 * @author polidog <polidog@gmail.com>
 * @version 0.1
 */
Abstract Class Driver {

	protected $config;

	/**
	 * サポートしてるかどうかのフラグ
	 * @var type 
	 */
	protected $isSupported = false;

	/**
	 * コンストラクタ
	 * @param array $config
	 * @throws \Ultracache\Exception\NosupportDriverException
	 */
	public function __construct(array $config) {
		$this->config = $config;
		$this->init($config);

		if (!$this->isSuppoeted()) {
			throw new NosupportDriverException('no support driver name:' . get_class($this));
		}
	}

	/**
	 * サポートしてるかどうかのフラグ
	 * @return type
	 */
	public function isSuppoeted() {
		return $this->isSupported;
	}

	abstract protected function init(array $config);

	abstract public function get($key);

	abstract public function set($key, $value, $exper = null);

	abstract public function delete($key);
}