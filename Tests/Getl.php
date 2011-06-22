<?php
require_once 'PHPUnit/Framework.php';
require_once 'Include/Utility.php';

abstract class Getl_TestCase extends ZStore_TestCase {
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		// same client
		$instance->set($testKey, $testValue, $testFlags);
   		$returnFlags = null;
   		$returnValue = $instance->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
		// different client
		$instance->set($testKey, $testValue, $testFlags);
   		$returnFlags = null;
   		$returnValue = $instance2->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
		//release lock
		$instance2->set($testKey, $testValue);
	}
	
 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Set($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();

		// same client
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey);
		$success = $instance->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");
		
		// different client
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey);
		$success = $instance2->set($testKey, $testValue, $testFlags);
   		$this->assertFalse($success, "Memcache::set (negative)");
		
		//release lock
		$instance->set($testKey, $testValue);
	} 
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Getl($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		// same client
		$instance->set($testKey, $testValue, $testFlags);
   		$returnFlags = null;
		$instance->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
   		$returnValue = $instance->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
		$this->assertFalse($returnValue, "Memcache::get (negative)");
		
		// different client
		$instance->set($testKey, $testValue, $testFlags);
   		$returnFlags = null;
		$instance->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
   		$returnValue = $instance2->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
		$this->assertFalse($returnValue, "Memcache::get (negative)");
		
		//release lock
		$instance->set($testKey, $testValue);
	}
	
	/**
     * @dataProvider keyProvider
    */
	public function test_Getl_GetNonExistingValue($testKey) {
		
		$instance = $this->sharedFixture;
		
		// negative get test
   		$returnValue = $instance->getl($testKey);
		$this->assertNull($returnValue, "Memcache::get (negative)");
	}
	
	
	/**
     * @dataProvider keyProvider
     */
	public function test_Getl_GetNullOnKeyMiss($testKey) {

		$instance = $this->sharedFixture;

		$instance->setproperty("NullOnKeyMiss", true);
		
   		// validate added value
   		$returnValue = $instance->getl($testKey);
   		$this->assertNull($returnValue, "Memcache::get (negative)");
	} 
	
	/**
     * @dataProvider keyProvider
     */
	public function test_Getl_GetNullOnKeyMissBadConnection($testKey) {

		$instance = $this->sharedFixture;
		
		// bogus connection
		$testHost = Utility::getHost();
		$instance = new Memcache;
		@$instance->addServer("192.168.168.192");
		@$instance->setproperty("NullOnKeyMiss", true);
				
   		// validate added value
   		$returnValue = @$instance->getl($testKey);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
	} 

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Get($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();

   		// same client
		$instance->set($testKey, $testValue, $testFlags);
   		$returnFlags = null;
		$instance->getl($testKey);
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
		// different client
   		$returnValue = $instance2->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
		//release lock
		$instance->set($testKey, $testValue);
	}  
	
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Get2($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey);
		
   		// same client
   		$returnFlags = null;
   		$returnValue = null;
   		$success = $instance->get2($testKey, $returnValue, $returnFlags);
   		$this->assertTrue($success, "Memcache::get2 (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get2 (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get2 (flag)");
		
		// different client
		$returnFlags = null;
   		$returnValue = null;
   		$success = $instance2->get2($testKey, $returnValue, $returnFlags);
   		$this->assertTrue($success, "Memcache::get2 (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get2 (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get2 (flag)");
		
		//release lock
		$instance->set($testKey, $testValue);
	}	

	/**
     * @dataProvider keyValueProvider
	 * @expectedException PHPUnit_Framework_Error
     */
	public function test_Getl_Delete_Same_Client($testKey, $testValue) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();

		// same client
		$instance->set($testKey, $testValue);
		$instance->getl($testKey);
   		$success = $instance->delete($testKey);
		$this->assertFalse($success, "Memcache::delete (negative)");  		
   		 // verify key is present	
   		$returnValue = $instance->get($testKey);
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");

		//release lock
		$instance->set($testKey, $testValue);
	} 

	/**
     * @dataProvider keyValueProvider
	 * @expectedException PHPUnit_Framework_Error
     */
	public function test_Getl_Delete_Different_Client($testKey, $testValue) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		// different client
		$instance->set($testKey, $testValue);
		$instance->getl($testKey);
   		$success = $instance2->delete($testKey);
		$this->assertFalse($success, "Memcache::delete (negative)");  		
   		 // verify key is present	
   		$returnValue = $instance->get($testKey);
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");

		//release lock
		$instance->set($testKey, $testValue);
	} 	
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Replace($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		
   		// same client 
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
   		$success = $instance->replace($testKey, $testValue2, $testFlags);
   		$this->assertFalse($success, "Memcache::replace (negative)");
   		
					// validate value is not replaced
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertEquals($testValue1, $returnValue, "Memcache::get (value)");
		
		// different client
		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
   		$success = $instance2->replace($testKey, $testValue2, $testFlags);
   		$this->assertFalse($success, "Memcache::replace (negative)");
   		
					// validate value is not replaced
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertEquals($testValue1, $returnValue, "Memcache::get (value)");

		//release lock
		$instance->set($testKey, $testValue);
	}
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Add_Getl_Same_Client($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		
		$testValue1 = $testValue;
		
   		$instance->add($testKey, $testValue1, $testFlags);
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue1, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
		//release lock
		$instance->set($testKey, $testValue);
	}

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Add_Getl_Different_Client($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		
		$instance->add($testKey, $testValue1, $testFlags);
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance2->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue1, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
			
		//release lock
		$instance2->set($testKey, $testValue);
}
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Add_Getl_Add_Same_Client($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		
   		$instance->add($testKey, $testValue1, $testFlags);
   		$instance->getl($testKey);

		$success = $instance->add($testKey, $testValue2, $testFlags);
   		$this->assertFalse($success, "Memcache::replace (positive)");

		
		//release lock
		$instance->set($testKey, $testValue);
	}

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Add_Getl_Add_Different_Client($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		
   		$instance->add($testKey, $testValue1, $testFlags);
   		$instance->getl($testKey);

		$success = $instance2->add($testKey, $testValue2, $testFlags);
   		$this->assertFalse($success, "Memcache::add (negative)");
		
		//release lock
		$instance->set($testKey, $testValue);
	}
	
	/**
     * @dataProvider keyValueProvider
	 * @expectedException PHPUnit_Framework_Error
     */
	public function test_Getl_Increment_Same_Client($testKey, $testValue) {
   		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
   		$testValue1 = strlen($testValue);
		
   		// same client
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);

					// positive increment test
   		$returnValue = $instance->increment($testKey, $testValue1);
		$this->assertFalse($returnValue, "Memcache::increment (negative)");
   		$returnValue = null;
		$returnValue = $instance->get($testKey);
		$this->assertEquals($returnValue, $testValue1,  "Memcache::increment (negative)");
		
		//release lock
		$instance->set($testKey, $testValue);
	} 

	/**
     * @dataProvider keyValueProvider
	 * @expectedException PHPUnit_Framework_Error
     */
	public function test_Getl_Increment_Different_Client($testKey, $testValue) {
   		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
   		$testValue1 = strlen($testValue);
		
		// different client
		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);

					// verify value is not incremented
   		$returnValue = $instance2->increment($testKey, $testValue1);
		$this->assertFalse($returnValue, "Memcache::increment (negative)");
		$returnValue = null;
		$returnValue = $instance->get($testKey);
   		$this->assertEquals($returnValue, $testValue1,  "Memcache::increment (negative)");
		
		//release lock
		$instance->set($testKey, $testValue);
	} 
	
	/**
     * @dataProvider keyValueProvider
	 * @expectedException PHPUnit_Framework_Error
     */
	public function test_Getl_Decrement_Same_Client($testKey, $testValue) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
   		$testValue1 = strlen($testValue);
   		
   		// same client
   		$instance->set($testKey, $testValue1 * 2);
		$instance->getl($testKey);
					// positive decrement test
   		$returnValue = $instance->decrement($testKey, $testValue1);
		$this->assertFalse($returnValue, "Memcache::decrement (negative)");
		$returnValue = null;
		$returnValue = $instance->get($testKey);
   		$this->assertEquals($returnValue, $testValue1 * 2,  "Memcache::decrement (negative)");
			
		//release lock
		$instance->set($testKey, $testValue);
	}

	/**
     * @dataProvider keyValueProvider
	 * @expectedException PHPUnit_Framework_Error
     */
	public function test_Getl_Decrement_Different_Client($testKey, $testValue) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
   		$testValue1 = strlen($testValue);
   		
		// different client
		$instance->set($testKey, $testValue1 * 2);
		$instance->getl($testKey);
					// verify value is not decremented
   		$returnValue = $instance2->decrement($testKey, $testValue1);
		$this->assertFalse($returnValue, "Memcache::decrement (negative)");
		$returnValue = null;
		$returnValue = $instance->get($testKey);
   		$this->assertEquals($returnValue, $testValue1 * 2,  "Memcache::decrement (negative)");
		
		//release lock
		$instance->set($testKey, $testValue);
	}	
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_SetTTL($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testTTL = 3;
		
		// same client
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey);
		$success = $instance->set($testKey, $testValue, $testFlags, $testTTL);
   		$this->assertTrue($success, "Memcache::set (positive)");
					// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
		// different client
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey);
		$success = $instance2->set($testKey, $testValue, $testFlags, $testTTL);
   		$this->assertFalse($success, "Memcache::set (positive)");
					// validate key is not expired
		sleep($testTTL + 1);
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
				
		//release lock
		$instance->set($testKey, $testValue);
	}

	/**
     * @dataProvider keyValueFlagsProvider
     */

	public function test_Getl_SetTTLExpired_Same_Client($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testTTL = 3;
		
		// positive set test
		$instance->set($testKey, $testValue, $testFlags, $testTTL);
		$instance->getl($testKey);
		
		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
   		
   		sleep($testTTL + 1);
   		
   		// validate set value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
	}

   	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function est_AddTTLExpired_Same_Client($testKey, $testValue, $testFlags) { //commented for bug 3252

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();

		$testTTL = 3;
		
   		// positive add test 
   		$instance->add($testKey, $testValue, $testFlags, $testTTL);
		$returnValue = $instance->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
   		sleep($testTTL+1);
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
		
	}
	
   	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function est_ReplaceTTLExpired_Same_Client($testKey, $testValue, $testFlags) { //commented for bug 3252

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();

		$testTTL = 3;
			
   		// replace and getl 
		$instance->set($testKey, $testValue, $testFlags);
   		$instance->replace($testKey, $testValue, $testFlags, $testTTL);
		$returnValue = $instance->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
   		sleep($testTTL+1);
   		
   		// validate replaced value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");

		// getl and replace with expiry 
   		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey);
		$success = $instance->replace($testKey, $testValue, $testFlags, $testTTL);
   		$this->assertFalse($success, "Memcache::replace (positive)");
   		
   		sleep($testTTL+1);
   		
   		// validate value is not replaced 
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");

	}

	/**
     * @dataProvider keyValueFlagsProvider
     */

	public function test_Getl_SetTTLExpired_Different_Client($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testTTL = 3;
		
		//  set expiry and getl
		$instance->set($testKey, $testValue, $testFlags, $testTTL);
   		$returnValue = $instance2->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
   		
   		sleep($testTTL + 1);
   		
   		// validate set value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
		
		//  getl and set expiry 
		$instance->set($testKey, $testValue, $testFlags);
		$instance2->getl($testKey);
		$success = $instance2->set($testKey, $testValue, $testFlags, $testTTL);
   		$this->assertTrue($success, "Memcache::set (positive)");
   		
   		sleep($testTTL + 1);
   		
   		// validate set value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
	}
	
 
	
			//***** Evict and getl ***** ////
		

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Evict_Getl($testKey, $testValue, $testFlags) {
		$instance = $this->sharedFixture;
		
		$instance->set($testKey, $testValue, $testFlags);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);

   		$returnFlags = null;
   		$returnValue = $instance->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	}

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Evict($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance->set($testKey, $testValue, $testFlags);
		sleep(1);
		$instance->getl($testKey);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);

   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	}

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Evict_Getl($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance->set($testKey, $testValue, $testFlags);
		sleep(1);
		$instance->getl($testKey);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);

   		$returnFlags = null;
   		$returnValue = $instance->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
	}
	
	
 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Evict_Set($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();

		// set from same client
		$instance->set($testKey, $testValue, $testFlags);
		sleep(1);
		$instance->getl($testKey);
   		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
		$success = $instance->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");

		// set from different client
		$instance->set($testKey, $testValue, $testFlags);
		sleep(1);
		$instance->getl($testKey);
   		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
		$success = $instance2->set($testKey, $testValue, $testFlags);
   		$this->assertFalse($success, "Memcache::set (negative)");

	} 

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Evict_Get($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
   		
   		// get from same client
		$instance->set($testKey, $testValue, $testFlags);
		sleep(1);
		$instance->getl($testKey);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
		
		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		   		
   		// get from different client
		$instance->set($testKey, $testValue, $testFlags);
		sleep(1);
   		$returnFlags = null;
		$instance->getl($testKey);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
		
   		$returnValue = $instance2->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	}  

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Evict_Get2($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		// same client
		$instance->set($testKey, $testValue, $testFlags);
		sleep(1);
		$instance->getl($testKey);
   		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
   
   		$returnFlags = null;
   		$returnValue = null;
   		$success = $instance->get2($testKey, $returnValue, $returnFlags);
   		$this->assertTrue($success, "Memcache::get2 (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get2 (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get2 (flag)");

		// different client
		$instance->set($testKey, $testValue, $testFlags);
		sleep(1);
		$instance->getl($testKey);
   		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
   
   		$returnFlags = null;
   		$returnValue = null;
   		$success = $instance2->get2($testKey, $returnValue, $returnFlags);
   		$this->assertTrue($success, "Memcache::get2 (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get2 (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get2 (flag)");
	}	

		/**
     * @dataProvider keyValueProvider
	 * @expectedException PHPUnit_Framework_Error
     */
	public function test_Getl_Evict_Delete($testKey, $testValue) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();

		// same client
		$instance->set($testKey, $testValue);
		sleep(1);
		$instance->getl($testKey);
  		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
   		$success = $instance->delete($testKey);
		$this->assertFalse($success, "Memcache::delete (negative)");  		
   		 // verify key is present	
   		$returnValue = $instance->get($testKey);
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
	
		// different client
		$instance->set($testKey, $testValue);
		sleep(1);
		$instance->getl($testKey);
  		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
   		$success = $instance2->delete($testKey);
		$this->assertFalse($success, "Memcache::delete (negative)");  		
   		 // verify key is present	
   		$returnValue = $instance->get($testKey);
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
	} 
	
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Evict_Replace($testKey, $testValue, $testFlags) {
		
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
   		// positive replace test
   		$success = $instance->replace($testKey, $testValue2, 0);
   		$this->assertFalse($success, "Memcache::replace (negative)");
   		
   		// validate value not replaced 
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
   		// positive replace test
   		$success = $instance2->replace($testKey, $testValue2, 0);
   		$this->assertFalse($success, "Memcache::replace (negative)");
   		
   		// validate value is not replaced
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flags)");
	}


		/**
     * @dataProvider keyValueProvider
	 * @expectedException PHPUnit_Framework_Error
     */
	public function test_Getl_Evict_Increment($testKey, $testValue) {
   		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
   		$testValue1 = strlen($testValue);
		
   		// same client
   		$instance->set($testKey, $testValue1);
		sleep(1);
		$instance->getl($testKey);
  		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
   		$returnValue = $instance->increment($testKey, $testValue1);
   		$this->assertFalse($returnValue, "Memcache::increment (negative)");
		$returnValue = null;
		$returnValue = $instance->get($testKey);
   		$this->assertEquals($returnValue, $testValue1,  "Memcache::increment (negative)");
		
		// different client
   		$instance->set($testKey, $testValue1);
		sleep(1);
		$instance->getl($testKey);
  		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
		$returnValue = $instance2->increment($testKey, $testValue1);
		$this->assertFalse($returnValue, "Memcache::increment (negative)");
		$returnValue = null;
		$returnValue = $instance->get($testKey);		
   		$this->assertEquals($returnValue, $testValue1,  "Memcache::increment (negative)");
		
	} 
	
	/**
     * @dataProvider keyValueProvider
	 * @expectedException PHPUnit_Framework_Error
     */
	public function test_Getl_Evict_Decrement($testKey, $testValue) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
   		$testValue1 = strlen($testValue);
   		
   		// same client
   		$instance->set($testKey, $testValue1 * 2);
		sleep(1);
		$instance->getl($testKey);
  		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
   		$returnValue = $instance->decrement($testKey, $testValue1);
   		$this->assertFalse($returnValue, "Memcache::decrement (negative)");
		$returnValue = null;
		$returnValue = $instance->get($testKey);		
   		$this->assertEquals($returnValue, $testValue1 * 2,  "Memcache::decrement (negative)");
		
		// different client
   		$instance->set($testKey, $testValue1 * 2);
		sleep(1);
		$instance->getl($testKey);
  		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
		$returnValue = $instance2->decrement($testKey, $testValue1);
		$this->assertFalse($returnValue, "Memcache::decrement (negative)");
		$returnValue = null;
		$returnValue = $instance->get($testKey);		
   		$this->assertEquals($returnValue, $testValue1 * 2,  "Memcache::decrement (negative)");
	}
	
		/**
     * @dataProvider keyValueFlagsProvider
     */

	public function test_SetTTLExpired_Getl_Evict($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		
		$testTTL = 3;
		
		// positive set test
		$instance->set($testKey, $testValue, $testFlags, $testTTL);
		sleep(1);
		$instance->getl($testKey);
  		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
   		sleep($testTTL);
   		
   		// validate set value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
	}
		
		//***** getl and update to check lock is released ***** ////
		
		
 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Set_Update($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey);
		$instance->set($testKey, $testValue, $testFlags);
		$success = $instance2->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");

	} 
	
		/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Update_Getl($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$instance->set($testKey, $testValue, $testFlags);
   		$returnFlags = null;
		$instance->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
		$instance->set($testKey, $testValue, $testFlags);
   		$returnValue = $instance2->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
		// release lock
		$instance2->set($testKey, $testValue);
	}
	
	/**
     * @dataProvider keyValueProvider
     */
	public function test_Getl_Update_Delete($testKey, $testValue) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();

		$instance->set($testKey, $testValue);
		$instance->getl($testKey);
		$instance->set($testKey, $testValue);
   		$success = $instance2->delete($testKey);
		$this->assertTrue($success, "Memcache::delete (positive)");  		
   		 // verify key is not present	
   		$returnValue = $instance->get($testKey);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
		
	} 
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Update_Replace($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		 
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
		$instance->set($testKey, $testValue1);
   		$success = $instance2->replace($testKey, $testValue2, $testFlags);
   		$this->assertTrue($success, "Memcache::replace (positive)");
 
    		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue2, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	}

	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Add_Getl_Update($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		
   		// positive add test 
   		$instance->add($testKey, $testValue1, $testFlags);
		$instance->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
		$instance->set($testKey, $testValue, $testFlags);
		$success = $instance2->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");
	}
	
	/**
     * @dataProvider keyValueProvider
     */
	public function test_Getl_Increment_Update($testKey, $testValue) {
   		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
   		$testValue1 = strlen($testValue);
		
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
		$instance->set($testKey, $testValue1);
   		$returnValue = $instance2->increment($testKey, $testValue1);
   		$this->assertEquals($returnValue, 2 * $testValue1,  "Memcache::increment (positive)");
	
	} 
	
	/**
     * @dataProvider keyValueProvider
     */
	public function test_Getl_Decrement_Update($testKey, $testValue) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
   		$testValue1 = strlen($testValue);
   		
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
		$instance->set($testKey, $testValue1 * 2);
   		$returnValue = $instance2->decrement($testKey, $testValue1);
   		$this->assertEquals($returnValue, $testValue1,  "Memcache::decrement (positive)");
		
	}
	
		/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_SetTTL_Update($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testTTL = 3;
		
		$instance->set($testKey, "test_value", $testFlags);
		$instance->getl($testKey);
		$instance->set($testKey, "test_value", $testFlags);
		$success = $instance2->set($testKey, $testValue, $testFlags, $testTTL);
   		$this->assertTrue($success, "Memcache::set (positive)");
					// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
		sleep($testTTL + 1);
   		
   		// validate set value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");	
	}

	// ****** test lock after getl timeout	 **** //

 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Check_Timeout($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$timeout = 3 ;
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey, $timeout);
		sleep($timeout + 1);
		$success = $instance2->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");

	} 	
	
 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Default_Timeout($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey);
		sleep(16);
		$success = $instance2->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");

	} 	

	 /**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Max_Timeout($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey, 30);
		sleep(31);
		$success = $instance2->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");

	} 

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Greater_Than_Max_Timeout($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey, 60);
		sleep(16);						// For timeout values more 30, membase converts it to 15 seconds
		$success = $instance2->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");

	} 	
         /**
     * @dataProvider keyValueFlagsProvider
     */
        public function test_Getl_Multiple_Request_Max_Timeout($testKey, $testValue, $testFlags) {

                $instance = $this->sharedFixture;
                $instance2 = Utility::getMaster();

                $instance->set($testKey, $testValue, $testFlags);
                $instance->getl($testKey, 18);
                $start_time = time();
                $returnvalue = 0;
                while( $returnvalue != 1)
                {
                        $returnvalue = $instance2->set($testKey, $testValue, $testFlags);
                }
                $end_time = time() - $start_time;
                if (($end_time == 19) or ($end_time == 18))
                {
                        $success = True;
                }
                else
                {
                        $success = False;
                }
                $this->assertTrue($success, "Memcache::set (positive)");

        }
 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Set_Timeout($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey, $GLOBALS['getl_timeout']);
		sleep($GLOBALS['getl_timeout'] + 1);
		$success = $instance2->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");

	} 
	
		/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Timeout_Getl($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$instance->set($testKey, $testValue, $testFlags);
   		$returnFlags = null;
		$instance->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
		sleep($GLOBALS['getl_timeout'] + 1);
   		$returnValue = $instance2->getl($testKey, 3, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
		// release lock
		$instance2->set($testKey, $testValue);
	}
	
		/**
     * @dataProvider keyValueProvider
     */
	public function test_Getl_Timeout_Delete($testKey, $testValue) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();

		$instance->set($testKey, $testValue);
		$instance->getl($testKey, $GLOBALS['getl_timeout']);
		sleep($GLOBALS['getl_timeout'] + 1);
   		$success = $instance2->delete($testKey);
		$this->assertTrue($success, "Memcache::delete (positive)");  		
   		 // verify key is not present	
   		$returnValue = $instance->get($testKey);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
		
	} 
	
		/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_Timeout_Replace($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		 
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey, $GLOBALS['getl_timeout']);
		sleep($GLOBALS['getl_timeout'] + 1);
   		$success = $instance2->replace($testKey, $testValue2, $testFlags);
   		$this->assertTrue($success, "Memcache::replace (positive)");
 
     		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue2, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	}
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Add_Getl_Timeout($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		
   		// positive add test 
   		$instance->add($testKey, $testValue1, $testFlags);
		$instance->getl($testKey, $GLOBALS['getl_timeout'], $returnFlags);
		sleep($GLOBALS['getl_timeout'] + 1);
		$success = $instance2->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");
	}
	
	/**
     * @dataProvider keyValueProvider
     */
	public function test_Getl_Increment_Timeout($testKey, $testValue) {
   		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
   		$testValue1 = strlen($testValue);
		
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey, $GLOBALS['getl_timeout']);
		sleep($GLOBALS['getl_timeout'] + 1);
   		$returnValue = $instance2->increment($testKey, $testValue1);
   		$this->assertEquals($returnValue, 2 * $testValue1,  "Memcache::increment (positive)");
	
	} 
	
	/**
     * @dataProvider keyValueProvider
     */
	public function test_Getl_Decrement_Timeout($testKey, $testValue) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
   		$testValue1 = strlen($testValue);
   		
   		$instance->set($testKey, $testValue1 * 2);
		$instance->getl($testKey, $GLOBALS['getl_timeout']);
		sleep($GLOBALS['getl_timeout'] + 1);
   		$returnValue = $instance2->decrement($testKey, $testValue1);
   		$this->assertEquals($returnValue, $testValue1,  "Memcache::decrement (positive)");
		
	}
	
		/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_SetTTL_Timeout($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testTTL = 3;
		
		$instance->set($testKey, "test_value", $testFlags);
		$instance->getl($testKey, $GLOBALS['getl_timeout']);
		sleep($GLOBALS['getl_timeout'] + 1);
		$success = $instance2->set($testKey, $testValue, $testFlags, $testTTL);
   		$this->assertTrue($success, "Memcache::set (positive)");
					// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
		sleep($testTTL + 1);
   		
   		// validate set value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");

	}

		//**** unlock ***** //
		
 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Unlock($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey);
		$success = $instance->unlock($testKey);
		$this->assertTrue($success, "Memcache::unlockock (positive)");

	} 		

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Getl_After_Unlock($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey);
		$instance->unlock($testKey);
		
		// same client
   		$returnValue = $instance->getl($testKey);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
		$instance->unlock($testKey);
		
		// different client
   		$returnValue = $instance2->getl($testKey);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
		$success = $instance2->unlock($testKey);
		$this->assertTrue($success, "Memcache::unlockock (positive)");

	} 
	
 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Unlock_Negative($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$success = $instance->unlock($testKey);
		$this->assertFalse($success, "Memcache::unlockock (negative)");
		
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey);
		$instance->unlock($testKey);
		$success = $instance->unlock($testKey);
		$this->assertFalse($success, "Memcache::unlockock (negative)");

	} 	

 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Unlock_After_Timeout($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey, $GLOBALS['getl_timeout']);
		sleep($GLOBALS['getl_timeout'] + 1);
		$success = $instance->unlock($testKey);
		$this->assertFalse($success, "Memcache::unlockock (negative)");
	} 
	
	 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Unlock_From_Different_Client($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$instance->set($testKey, $testValue, $testFlags);
		$instance->getl($testKey);
		$success = $instance2->unlock($testKey);
		$this->assertFalse($success, "Memcache::unlockock (negative)");
		$success = $instance2->set($testKey, $testValue, $testFlags);
   		$this->assertFalse($success, "Memcache::set (negative)");
		$instance->unlock($testKey);
		$this->assertFalse($success, "Memcache::unlockock (positive)");
	} 
	
		//***** getl and unlock. Check lock is released ***** ////
		
		
 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Unlock_Set($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$instance->set($testKey, $testValue, $testFlags);
			// same client
		$instance->getl($testKey);
		$instance->unlock($testKey);
		$success = $instance->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");
			// different client
		$instance->getl($testKey);
		$instance->unlock($testKey);
		$success = $instance2->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");
			// both clients
		$instance->getl($testKey);
		$instance->unlock($testKey);
		$instance2->set($testKey, $testValue, $testFlags);
		$success = $instance->set($testKey, $testValue, $testFlags);		
   		$this->assertTrue($success, "Memcache::set (positive)");
			
	} 

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Unlock_Get($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();

		$instance->set($testKey, $testValue, $testFlags);
   		$returnFlags = null;
		$instance->getl($testKey);
		$instance->unlock($testKey);
		// same client
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
		// different client
   		$returnValue = $instance2->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");

	}  

	/**
     * @dataProvider keyValueProvider
     */
	public function test_Unlock_Delete($testKey, $testValue) {

		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		// same client
		$instance->set($testKey, $testValue);
		$instance->getl($testKey);
		$instance->unlock($testKey);
   		$success = $instance->delete($testKey);
		$this->assertTrue($success, "Memcache::delete (positive)");  		
   		 // verify key is not present	
   		$returnValue = $instance->get($testKey);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
		// different client
		$instance->set($testKey, $testValue);
		$instance->getl($testKey);
		$instance->unlock($testKey);
   		$success = $instance2->delete($testKey);
		$this->assertTrue($success, "Memcache::delete (positive)");  		
   		 // verify key is not present	
   		$returnValue = $instance->get($testKey);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
		
	} 
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Unlock_Replace($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		 
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
		$instance->unlock($testKey);
   		$success = $instance2->replace($testKey, $testValue2, $testFlags);
   		$this->assertTrue($success, "Memcache::replace (positive)");
		
		   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue2, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
 
	}



	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Add_Unlock($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
		$testValue1 = $testValue;
		
   		// positive add test 
   		$instance->add($testKey, $testValue1, $testFlags);
		$instance->getl($testKey);
		$instance->unlock($testKey);
		$success = $instance2->add($testKey, $testValue, $testFlags);
   		$this->assertFalse($success, "Memcache::add (negative)");		
		$success = $instance2->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");
	}
	
	/**
     * @dataProvider keyValueProvider
     */
	public function test_Unlock_Increment($testKey, $testValue) {
   		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
   		$testValue1 = strlen($testValue);
		
   		$instance->set($testKey, $testValue1);
		$instance->getl($testKey);
		$instance->unlock($testKey);
   		$returnValue = $instance2->increment($testKey, $testValue1);
   		$this->assertEquals($returnValue, 2 * $testValue1,  "Memcache::increment (positive)");
	
	} 
	
	/**
     * @dataProvider keyValueProvider
     */
	public function test_Unlock_Decrement($testKey, $testValue) {
		
		$instance = $this->sharedFixture;
		$instance2 = Utility::getMaster();
		
   		$testValue1 = strlen($testValue);
   		
   		$instance->set($testKey, $testValue1 * 2);
		$instance->getl($testKey);
		$instance->unlock($testKey);
   		$returnValue = $instance2->decrement($testKey, $testValue1);
   		$this->assertEquals($returnValue, $testValue1,  "Memcache::decrement (positive)");
		
	}
	
}


class Getl_TestCase_Full extends Getl_TestCase
{
	public function keyProvider() {
		return Utility::provideKeys();
	}

	public function keyValueProvider() {
		return Utility::provideKeyValues();
	}

	public function keyValueFlagsProvider() {
		return Utility::provideKeyValueFlags();
	}
	
	public function flagsProvider() {
		return Utility::provideFlags();	
	}
}

?>
