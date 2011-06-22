<?php
require_once 'PHPUnit/Framework.php';
require_once 'Include/Utility.php';
	
abstract class Append_Prepend_TestCase extends ZStore_TestCase {


	/**
     * @dataProvider keyValueProvider
    */
	public function test_Append_NonExistingValue($testKey, $testValue) {

		$instance = $this->sharedFixture;
		
   		// negative Append test
   		$success = $instance->append($testKey, $testValue);
   		$this->assertFalse($success, "Memcache::append (negative)");
	}

	/**
     * @dataProvider keyValueProvider
    */
	public function test_Append_Expired_Key($testKey, $testValue) {

		$instance = $this->sharedFixture;
		
		$instance->set($testKey, $testValue, 0, 2);
		sleep(3);
   		// negative Append test
   		$success = $instance->append($testKey, $testValue);
   		$this->assertFalse($success, "Memcache::append (negative)");
		
		$returnValue = $instance->get($testKey);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
	}
		
	/**
     * @dataProvider keyValueProvider
    */
	public function test_Append_Deleted_Key($testKey, $testValue) {

		$instance = $this->sharedFixture;
		
		$instance->set($testKey, $testValue);
		$instance->delete($testKey);
   		// negative Append test
   		$success = $instance->append($testKey, $testValue);
   		$this->assertFalse($success, "Memcache::append (negative)");
		
		$returnValue = $instance->get($testKey);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
	}
	
 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Append($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;

		// positive append test
		$instance->set($testKey, $testValue, $testFlags);
		$success = $instance->append($testKey, "testValue");
   		$this->assertTrue($success, "Memcache::append (positive)");
		
		   // validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue."testValue", $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	} 
	
	/**
     * @dataProvider keyValueProvider
    */
	public function test_Prepend_NonExistingValue($testKey, $testValue) {

		$instance = $this->sharedFixture;
		
   		// negative prepend test
   		$success = $instance->prepend($testKey, $testValue);
   		$this->assertFalse($success, "Memcache::prepend (negative)");
	}

	/**
     * @dataProvider keyValueProvider
    */
	public function test_Prepend_Expired_Key($testKey, $testValue) {

		$instance = $this->sharedFixture;
		
		$instance->set($testKey, $testValue, 0, 2);
		sleep(3);
   		// negative Prepend test
   		$success = $instance->prepend($testKey, $testValue);
   		$this->assertFalse($success, "Memcache::prepend (negative)");
		
		$returnValue = $instance->get($testKey);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
	}

	/**
     * @dataProvider keyValueProvider
    */
	public function test_Prepend_Deleted_Key($testKey, $testValue) {

		$instance = $this->sharedFixture;
		
		$instance->set($testKey, $testValue);
		$instance->delete($testKey);
   		// negative Prepend test
   		$success = $instance->prepend($testKey, $testValue);
   		$this->assertFalse($success, "Memcache::prepend (negative)");
		
		$returnValue = $instance->get($testKey);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
	}	
	
 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Prepend($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;

		// positive prepend test
		$instance->set($testKey, $testValue, $testFlags);
		$success = $instance->prepend($testKey, "testValue");
   		$this->assertTrue($success, "Memcache::prepend (positive)");
		
		   // validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals("testValue".$testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	} 
	
// disk greater than memory


	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Evict_Append($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		
   		// positive add test 
   		$instance->set($testKey, $testValue1);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
   		   		
   		// positive append test
		$success = $instance->append($testKey, "testValue");
   		$this->assertTrue($success, "Memcache::append (positive)");
		
		   // validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue."testValue", $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	}
	
		/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Evict_Prepend($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		
   		// positive add test 
   		$instance->set($testKey, $testValue1);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
   		   		
   		// positive append test
		$success = $instance->prepend($testKey, "testValue");
   		$this->assertTrue($success, "Memcache::prepend (positive)");
		
		   // validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals("testValue".$testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	}

	// getl
	
	
	/**
     * @dataProvider keyValueFlagsProvider
	 * @expectedException PHPUnit_Framework_Error
     */
	public function test_Getl_Append($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		
   		// same client 
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
   		$success = $instance->append($testKey, $testValue2);
   		$this->assertFalse($success, "Memcache::append (negative)");
   		
					// validate value is not replaced
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertEquals($testValue1, $returnValue, "Memcache::get (value)");
		
		// different client
		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
   		$success = $instance2->append($testKey, $testValue2, $testFlags);
   		$this->assertFalse($success, "Memcache::append (negative)");
   		
					// validate value is not replaced
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertEquals($testValue1, $returnValue, "Memcache::get (value)");

		//release lock
		$instance->set($testKey, $testValue);
	}
	
	/**
     * @dataProvider keyValueFlagsProvider
	 * @expectedException PHPUnit_Framework_Error
     */
	public function test_Getl_Prepend($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		
   		// same client 
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
   		$success = $instance->prepend($testKey, $testValue2);
   		$this->assertFalse($success, "Memcache::prepend (negative)");
   		
					// validate value is not replaced
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertEquals($testValue1, $returnValue, "Memcache::get (value)");
		
		// different client
		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
   		$success = $instance2->prepend($testKey, $testValue2, $testFlags);
   		$this->assertFalse($success, "Memcache::prepend (negative)");
   		
					// validate value is not replaced
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertEquals($testValue1, $returnValue, "Memcache::get (value)");

		//release lock
		$instance->set($testKey, $testValue);
	}

	
	/**
     * @dataProvider keyValueFlagsProvider
	 * @expectedException PHPUnit_Framework_Error
     */
	public function test_Getl_Evict_Append($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		
   		// same client
   		$instance->set($testKey, $testValue1, $testFlags);
		sleep(1);
		$instance->getl($testKey);
  		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);		
   		
   		$success = $instance->append($testKey, $testValue2, 0);
   		$this->assertFalse($success, "Memcache::append (negative)");
   		
   		// validate value not appended 
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertEquals($testValue1, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flags)");
		
		// different client
   		$instance->set($testKey, $testValue1, $testFlags);
		sleep(1);
		$instance->getl($testKey);
  		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);		
   		
   		$success = $instance2->append($testKey, $testValue2, 0);
   		$this->assertFalse($success, "Memcache::append (negative)");
   		
   		// validate value is not appended
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flags)");
	}


	/**
     * @dataProvider keyValueFlagsProvider
	 * @expectedException PHPUnit_Framework_Error
     */
	public function test_Getl_Evict_Prepend($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		
   		// same client
   		$instance->set($testKey, $testValue1, $testFlags);
		sleep(1);
		$instance->getl($testKey);
  		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);		
   		
   		$success = $instance->prepend($testKey, $testValue2, 0);
   		$this->assertFalse($success, "Memcache::prepend (negative)");
   		
   		// validate value not prepended 
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertEquals($testValue1, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flags)");
		
		// different client
   		$instance->set($testKey, $testValue1, $testFlags);
		sleep(1);
		$instance->getl($testKey);
  		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);		
   		
   		$success = $instance2->prepend($testKey, $testValue2, 0);
   		$this->assertFalse($success, "Memcache::prepend (negative)");
   		
   		// validate value is not prepended
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flags)");
	}		

	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Update_Append($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		 
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
		$instance->set($testKey, $testValue1);
   		$success = $instance2->append($testKey, $testValue2, $testFlags);
   		$this->assertTrue($success, "Memcache::append (positive)");
 
    		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue1.$testValue2, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	}
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Update_Prepend($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		 
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
		$instance->set($testKey, $testValue1);
   		$success = $instance2->prepend($testKey, $testValue2, $testFlags);
   		$this->assertTrue($success, "Memcache::prepend (positive)");
 
    		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue2.$testValue1, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	}

		/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Unlock_Append($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		 
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
		$instance->unlock($testKey);
   		$success = $instance2->append($testKey, $testValue2, $testFlags);
   		$this->assertTrue($success, "Memcache::append (positive)");
		
		   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue1.$testValue2, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
 
	}
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Unlock_Prepend($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		 
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
		$instance->unlock($testKey);
   		$success = $instance2->prepend($testKey, $testValue2, $testFlags);
   		$this->assertTrue($success, "Memcache::prepend (positive)");
		
		   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue2.$testValue1, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
 
	}

			/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Timeout_Append($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		 
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey, $GLOBALS['getl_timeout']);
		sleep($GLOBALS['getl_timeout'] + 1);
   		$success = $instance2->append($testKey, $testValue2, $testFlags);
   		$this->assertTrue($success, "Memcache::replace (positive)");
 
     		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue1.$testValue2, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	}

			/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Timeout_Prepend($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		 
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey, $GLOBALS['getl_timeout']);
		sleep($GLOBALS['getl_timeout'] + 1);
   		$success = $instance2->prepend($testKey, $testValue2, $testFlags);
   		$this->assertTrue($success, "Memcache::replace (positive)");
 
     		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue2.$testValue1, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	}	
	
// replication 
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Replication_append($testKey, $testValue, $testFlags) {
		
		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		
   		// positive add test 
   		$instance->set($testKey, $testValue1);
   		   		
   		// positive append test
   		$success = $instance->append($testKey, $testValue2, $testFlags);
   		sleep(2);
   		
   		// validate appended value
   		$returnFlags = null;
   		$returnValue = $instanceslave->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue1.$testValue2, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flags)");
	}

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Replication_prepend($testKey, $testValue, $testFlags) {
		
		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		
   		// positive add test 
   		$instance->set($testKey, $testValue1);
   		   		
   		// positive prepend test
   		$success = $instance->prepend($testKey, $testValue2, $testFlags);
   		sleep(2);
   		
   		// validate prepended value
   		$returnFlags = null;
   		$returnValue = $instanceslave->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue2.$testValue1, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flags)");
	}
	
}


class Append_Prepend_TestCase_Quick extends Append_Prepend_TestCase
{
	public function keyProvider() {
		return array(array("test_key"));
	}

	public function keyValueProvider() {
		return array(array("test_key", "test_value"));
	}

	public function keyValueFlagsProvider() {
		return array(array("test_key", "test_value", 0));
	}
	
	public function flagsProvider() {
		return Utility::provideFlags();	
	}
}

?>

