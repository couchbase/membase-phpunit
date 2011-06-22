<?php

ini_set("memcache.compression_level", 6);
ini_set("memory_limit", "128M");

require_once 'Include/Utility.php';
require_once 'PHPUnit/Framework.php';

abstract class ZStore_TestCase extends PHPUnit_Framework_TestCase {
	
	static $_passes = array();
	
	public function setUp() {
		if (isset(self::$_passes[$this->name])) {
			$this->markTestSkipped();
		}
		
   		$this->sharedFixture->setproperty("NullOnKeyMiss", false);
		
			// delete it before we start
		if (isset($this->data[0])) {
			$this->sharedFixture->set($this->data[0], "dummy");
			$this->sharedFixture->delete($this->data[0]);

		}
	}
	
	public function tearDown() {
		if ($this->status != PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
			self::$_passes[$this->name] = false;
		}
	}
}

class ZStoreTest extends PHPUnit_Framework_TestSuite {
	
	public static function suite() {
		Utility::prepareData();
		
		$suite = new ZStoreTest;
		
		$suite->addTestFile('Tests/Basic.php');
		$suite->addTestFile('Tests/CAS.php');
		$suite->addTestFile('Tests/Replication.php');
		$suite->addTestFile('Tests/Disk_Greater_than_Memory.php');
		$suite->addTestFile('Tests/Getl.php');
		$suite->addTestFile('Tests/Append_Prepend.php');
		if ($GLOBALS['testHost'] == "127.0.0.1" or $GLOBALS['testHost'] == php_uname("n")) $suite->addTestFile('Tests/Persistance.php');

		return $suite;
	}
	
	protected function setUp() {
		$host = Utility::getHost();
		
		$instance = new Memcache;
		$instance->addServer($host, $GLOBALS['testHostPort']);
	    $instance->setServerParams($host, $GLOBALS['testHostPort'], 1, 0);

		$this->sharedFixture = $instance;

	}
		
	protected function tearDown() {
		$this->sharedFixture = NULL;
	}
}
