<?php

require_once 'PHPUnit/Framework.php';
require_once 'Include/Utility.php';

abstract class Replication_TestCase extends ZStore_TestCase
{
   	/**
     * @dataProvider keyValueFlagsProvider
     */

	public function test_Replication_Set_Get($testKey, $testValue, $testFlags) {

	
	$instance = Utility::getMaster();
	$instanceslave = Utility::getSlave();
	$instanceslave2 = Utility::getSlave2();
		// add key 
	$instance->set($testKey, $testValue, $testFlags);
	sleep(2);
	
		// validate added value
	$returnFlags = null;
	$returnValue = $instanceslave->get($testKey, $returnFlags);
	$this->assertNotEquals($returnValue, false, "Memcache slave1::get (positive)");
	$this->assertEquals($testValue, $returnValue, "Memcache slave1::get (value)");
	$this->assertEquals($testFlags, $returnFlags, "Memcache slave1::get (flag)");

	$returnFlags2 = null;
	$returnValue2 = $instanceslave2->get($testKey, $returnFlags2);
	$this->assertNotEquals($returnValue2, false, "Memcache slave2::get (positive)");
	$this->assertEquals($testValue, $returnValue2, "Memcache slave2::get (value)");
	$this->assertEquals($testFlags, $returnFlags2, "Memcache slave2::get (flag)");
	}	 
	
	 /**
     * @dataProvider keyValueFlagsProvider 
     */

	public function test_Replication_Base64_Encode_Serialize($testKey, $testValue, $testFlags) {
	$instance = Utility::getMaster();
	$instanceslave = Utility::getSlave();
	$instanceslave2 = Utility::getSlave2();
	$testValue = base64_encode($testValue);
		// add key 
	$instance->set($testKey, $testValue, $testFlags);
	sleep(2);
	
		// validate added value
	$returnFlags = null;
	$returnValue = $instanceslave->get($testKey, $returnFlags);
	$this->assertNotEquals($returnValue, false, "Memcache slave1::get (positive)");
	$this->assertEquals($testValue, $returnValue, "Memcache slave1::get (value)");
	$this->assertEquals(base64_decode($testValue), base64_decode($returnValue), "Memcache slave1::get (value)");
	$this->assertEquals($testFlags, $returnFlags, "Memcache slave1::get (flag)");

	$returnFlags2 = null;
	$returnValue2 = $instanceslave2->get($testKey, $returnFlags2);
	$this->assertNotEquals($returnValue2, false, "Memcache slave2::get (positive)");
	$this->assertEquals($testValue, $returnValue2, "Memcache slave2::get (value)");
	$this->assertEquals(base64_decode($testValue), base64_decode($returnValue2), "Memcache slave2::get (value)");
	$this->assertEquals($testFlags, $returnFlags2, "Memcache slave2::get (flag)");	
	}

	/**
     * @dataProvider keyValueProvider
     */
	public function test_Replication_Delete($testKey, $testValue) {

		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		$instanceslave2 = Utility::getSlave2();

		// set reference value
		$instance->set($testKey, $testValue);
   		$success = $instance->delete($testKey);  
		sleep(2);	
  
   		$returnValue = $instanceslave->get($testKey);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
		$returnValue2 = $instanceslave2->get($testKey);
   		$this->assertFalse($returnValue2, "Memcache::get (negative)");
	} 
	
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Replication_Replace($testKey, $testValue, $testFlags) {
		
		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		$instanceslave2 = Utility::getSlave2();
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
 
   		$instance->set($testKey, $testValue1);
		$instance->replace($testKey, $testValue2, $testFlags);
   		sleep(2);
   		
   		// validate replaced value
   		$returnFlags = null;
   		$returnValue = $instanceslave->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue2, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flags)");
		
		$returnFlags2 = null;
   		$returnValue2 = $instanceslave2->get($testKey, $returnFlags2);
   		$this->assertNotEquals($returnValue2, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue2, $returnValue2, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags2, "Memcache::get (flags)");
	}


	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Replication_Add($testKey, $testValue, $testFlags) {
		
		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		$instanceslave2 = Utility::getSlave2();
		
   		// positive add test 
   		$success = $instance->add($testKey, $testValue, $testFlags);
   		sleep(2);
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instanceslave->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
		$returnFlags2 = null;
   		$returnValue2 = $instanceslave2->get($testKey, $returnFlags2);
   		$this->assertNotEquals($returnValue2, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue2, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags2, "Memcache::get (flag)");
	}
	
	
	/**
     * @dataProvider keyValueProvider
     */
	public function test_Replication_Increment($testKey, $testValue) {
   		
		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		$instanceslave2 = Utility::getSlave2();
		
   		$testValue1 = strlen($testValue);
		
   		// set initial value
   		$instance->set($testKey, $testValue1);
		
   		// positive increment test
   		$instance->increment($testKey, $testValue1);
		sleep(2);
		$returnValue = $instanceslave->get($testKey);
   		$this->assertEquals($returnValue, 2 * $testValue1,  "Memcache::increment (positive)");
		$returnValue2 = $instanceslave2->get($testKey);
   		$this->assertEquals($returnValue2, 2 * $testValue1,  "Memcache::increment (positive)");
	} 
	
	/**
     * @dataProvider keyValueProvider
     */
	public function test_Replication_Decrement($testKey, $testValue) {
		
		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		$instanceslave2 = Utility::getSlave2();
		
   		$testValue1 = strlen($testValue);
   		
   		// set initial value
   		$instance->set($testKey, $testValue1 * 2);

   		// positive decrement test
   		$instance->decrement($testKey, $testValue1);
		sleep(2);
		$returnValue = $instanceslave->get($testKey);
   		$this->assertEquals($returnValue, $testValue1,  "Memcache::decrement (positive)");
		$returnValue2 = $instanceslave2->get($testKey);
   		$this->assertEquals($returnValue2, $testValue1,  "Memcache::decrement (positive)");
	}

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Replication_SetTTL($testKey, $testValue, $testFlags) {
		
		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		$instanceslave2 = Utility::getSlave2();
		
		$testTTL = 30;
		
		// positive set test
		$instance->set($testKey, $testValue, $testFlags, $testTTL);
		sleep(2);
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instanceslave->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		
		$returnFlags2 = null;
   		$returnValue2 = $instanceslave2->get($testKey, $returnFlags2);
   		$this->assertNotEquals($returnValue2, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue2, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags2, "Memcache::get (flag)");
	}

	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Replication_Get2TTL($testKey, $testValue, $testFlags) {
		
		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		$instanceslave2 = Utility::getSlave2();
		
		$testTTL = 30;
		
		// positive set test
		$success = $instance->set($testKey, $testValue, $testFlags, $testTTL);
   		sleep(2);
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = null;
   		$success = $instanceslave->get2($testKey, $returnValue, $returnFlags);
   		$this->assertTrue($success, "Memcache::get2 (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get2 (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get2 (flag)");
   		$returnFlags2 = null;
   		$returnValue2 = null;
   		$success2 = $instanceslave2->get2($testKey, $returnValue2, $returnFlags2);
   		$this->assertTrue($success2, "Memcache::get2 (positive)");
   		$this->assertEquals($testValue, $returnValue2, "Memcache::get2 (value)");
   		$this->assertEquals($testFlags, $returnFlags2, "Memcache::get2 (flag)");		
	}
	
   	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Replication_AddTTL($testKey, $testValue, $testFlags) {

		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		$instanceslave2 = Utility::getSlave2();

		$testTTL = 30;
		
   		// positive add test 
   		$success = $instance->add($testKey, $testValue, $testFlags, $testTTL);
   		sleep(2);
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instanceslave->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
   		$returnFlags2 = null;
   		$returnValue2 = $instanceslave2->get($testKey, $returnFlags2);
   		$this->assertNotEquals($returnValue2, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue2, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags2, "Memcache::get (flag)");
	}
	
   	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Replication_ReplaceTTL($testKey, $testValue, $testFlags) {

		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		$instanceslave2 = Utility::getSlave2();

		$testTTL = 30;
		
		$instance->set($testKey, $testValue, $testFlags, $testTTL);
		
   		// positive add test 
   		$success = $instance->replace($testKey, $testValue, $testFlags, $testTTL);
   		sleep(2);
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instanceslave->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$returnFlags2 = null;
   		$returnValue2 = $instanceslave2->get($testKey, $returnFlags2);
   		$this->assertNotEquals($returnValue2, false, "Memcache::get (positive)");
		$this->assertEquals($testFlags, $returnFlags2, "Memcache::get (flag)");
		$this->assertEquals($testValue, $returnValue2, "Memcache::get (value)");		
	}
   		
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Replication_SetTTLExpired($testKey, $testValue, $testFlags) {
		
		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		$instanceslave2 = Utility::getSlave2();
		
		$testTTL = 3;
		
		// positive set test
		$success = $instance->set($testKey, $testValue, $testFlags, $testTTL);   		
   		sleep($testTTL + 2);
   		
   		// validate set value
   		$returnFlags = null;
   		$returnValue = $instanceslave->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
   		$returnFlags2 = null;
   		$returnValue2 = $instanceslave2->get($testKey, $returnFlags2);
   		$this->assertFalse($returnValue2, "Memcache::get (negative)");		
	}

   	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Replication_AddTTLExpired($testKey, $testValue, $testFlags) {

		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		$instanceslave2 = Utility::getSlave2();

		$testTTL = 3;
		
   		// positive add test 
   		$success = $instance->add($testKey, $testValue, $testFlags, $testTTL);
   		sleep($testTTL + 2);
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instanceslave->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
   		$returnFlags2 = null;
   		$returnValue2 = $instanceslave2->get($testKey, $returnFlags2);
   		$this->assertFalse($returnValue2, "Memcache::get (negative)");		
		
	}
	
   	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Replication_ReplaceTTLExpired($testKey, $testValue, $testFlags) {

		$instance = Utility::getMaster();
		$instanceslave = Utility::getSlave();
		$instanceslave2 = Utility::getSlave2();

		$testTTL = 3;
		
		$instance->set($testKey, $testValue, $testFlags, $testTTL);
		
   		// positive add test 
   		$success = $instance->replace($testKey, $testValue, $testFlags, $testTTL);
   		sleep($testTTL + 2);
   		
   		// validate replaced value
   		$returnFlags = null;
   		$returnValue = $instanceslave->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
   		$returnFlags2 = null;
   		$returnValue2 = $instanceslave2->get($testKey, $returnFlags2);
   		$this->assertFalse($returnValue2, "Memcache::get (negative)");		
	}

   	/**
     * @dataProvider keyValueFlagsProvider
     */

	public function test_Replication_Set_Delete_Multiple_times($testKey, $testValue, $testFlags) {

		// test to check vbucketmigrator doesn't break the connection
		
	$instance = Utility::getMaster();
	$instanceslave = Utility::getSlave();
	$instanceslave2 = Utility::getSlave2();
	
	$instance->set("keysetbeforestartingtest", "valuesetbeforestartingtest", 0);	
	for ($iCount = 0 ; $iCount < 100000 ; $iCount++ )
	{	
		$instance->set($testKey, $testValue, $testFlags);
		$instance->delete($testKey);
	}	
	
		// validate added value
	$returnFlags = null;
	$returnValue = $instanceslave->get("keysetbeforestartingtest");
	$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
	$this->assertEquals("valuesetbeforestartingtest", $returnValue, "Memcache::get (value)");
	$returnFlags2 = null;
	$returnValue2 = $instanceslave2->get("keysetbeforestartingtest");
	$this->assertNotEquals($returnValue2, false, "Memcache::get (positive)");
	$this->assertEquals("valuesetbeforestartingtest", $returnValue2, "Memcache::get (value)");	

	}		
	
}


class Replication_TestCase_Full extends Replication_TestCase
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
