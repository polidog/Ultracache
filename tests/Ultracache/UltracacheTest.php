<?php

/**
 * Test class for Ultracache
 *
 * @author polidog <polidogs@gmail.com>
 */
class UltracacheTest extends PHPUnit_Framework_TestCase {

	public function createCache() {
		
		return new \Ultracache\Ultracache();
	}
}