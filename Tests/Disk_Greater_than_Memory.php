<?php
require_once 'PHPUnit/Framework.php';
require_once 'Include/Utility.php';

abstract class Disk_Greater_than_Memory_TestCase extends ZStore_TestCase {
	
	
 	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Set_Evict_Set($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;

		// positive set test
		$instance->set($testKey, $testValue, $testFlags);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
		
		$success = $instance->set($testKey, $testValue, $testFlags);
   		$this->assertTrue($success, "Memcache::set (positive)");
	} 
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Evict_Get($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;

		$instance->set($testKey, $testValue, $testFlags);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	} 
	
	/**
     * @dataProvider keyValueProvider
     */
	public function test_Evict_Delete($testKey, $testValue) {

		$instance = $this->sharedFixture;

		// set reference value
		$instance->set($testKey, $testValue);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
  		
   		// cleanup (this shouldn't be here, but we need a full membase flush to get rid of this)
   		$success = $instance->delete($testKey);
		$this->assertTrue($success, "Memcache::delete (positive)");  		
   		   		
   		$returnValue = $instance->get($testKey);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
	} 
	
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Evict_Replace($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		
   		// positive add test 
   		$instance->set($testKey, $testValue1);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
   		   		
   		// positive replace test
   		$success = $instance->replace($testKey, $testValue2, $testFlags);
   		$this->assertTrue($success, "Memcache::replace (positive)");
   		
   		// validate replaced value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue2, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flags)");
	}
	

	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Evict_AddExistingValue($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		
		$testValue1 = $testValue;
		$testValue2 = strrev($testValue1);
		
   		$instance->set($testKey, $testValue1, $testFlags);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);

   		// positive add test
		$success = $instance->add($testKey, $testValue2, $testFlags);
   		$this->assertFalse($success, "Memcache::replace (positive)");
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue1, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	}
	
	/**
     * @dataProvider keyValueProvider
     */
	public function test_Evict_Increment($testKey, $testValue) {
   		
		$instance = $this->sharedFixture;
		
   		$testValue1 = strlen($testValue);
		
   		// set initial value
   		$instance->set($testKey, $testValue1);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);

   		// positive increment test
   		$returnValue = $instance->increment($testKey, $testValue1);
   		$this->assertEquals($returnValue, 2 * $testValue1,  "Memcache::increment (positive)");
	} 
	
	/**
     * @dataProvider keyValueProvider
     */
	public function test_Evict_Decrement($testKey, $testValue) {
		
		$instance = $this->sharedFixture;
		
   		$testValue1 = strlen($testValue);
   		
   		// set initial value
   		$instance->set($testKey, $testValue1 * 2);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);

   		// positive decrement test
   		$returnValue = $instance->decrement($testKey, $testValue1);
   		$this->assertEquals($returnValue, $testValue1,  "Memcache::decrement (positive)");
	}
	
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Evict_SetTTL($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		
		$testTTL = 30;
		
		$instance->set($testKey, $testValue, $testFlags);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
		
		// positive set test
		$success = $instance->set($testKey, $testValue, $testFlags, $testTTL);
   		$this->assertTrue($success, "Memcache::set (positive)");
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
   		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
   		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
	}
	
   	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Evict_ReplaceTTL($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;

		$testTTL = 30;
		
		$instance->set($testKey, $testValue, $testFlags, $testTTL);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
		
   		// positive add test 
   		$success = $instance->replace($testKey, $testValue, $testFlags, $testTTL);
   		$this->assertTrue($success, "Memcache::replace (positive)");
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertNotEquals($returnValue, false, "Memcache::get (positive)");
		$this->assertEquals($testFlags, $returnFlags, "Memcache::get (flag)");
		$this->assertEquals($testValue, $returnValue, "Memcache::get (value)");
	}
   		
	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Evict_SetTTLExpired($testKey, $testValue, $testFlags) {
		
		$instance = $this->sharedFixture;
		
		$testTTL = 1;
		
		// positive set test
		$success = $instance->set($testKey, $testValue, $testFlags, $testTTL);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
   		$this->assertTrue($success, "Memcache::set (positive)");
   		
   		sleep($testTTL + 1);
   		
   		// validate set value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
	}

   	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Evict_AddTTLExpired($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;

		$testTTL = 3;
		
   		// positive add test 
		$instance->add($testKey, $testValue, $testFlags, $testTTL);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
   		
   		sleep($testTTL+1);
   		
   		// validate added value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
		
	}
	
   	/**
     * @dataProvider keyValueFlagsProvider
     */
	public function test_Evict_ReplaceTTLExpired($testKey, $testValue, $testFlags) {

		$instance = $this->sharedFixture;

		$testTTL = 1;
		
		$instance->set($testKey, $testValue, $testFlags);
		sleep(1);
		shell_exec($GLOBALS['flushctl_path']." ".$GLOBALS['testHost'].":".$GLOBALS['testHostPort']." evict ".$testKey);
		sleep(1);
		
   		// positive add test 
   		$success = $instance->replace($testKey, $testValue, $testFlags, $testTTL);
   		$this->assertTrue($success, "Memcache::replace (positive)");
   		
   		sleep($testTTL+1);
   		
   		// validate replaced value
   		$returnFlags = null;
   		$returnValue = $instance->get($testKey, $returnFlags);
   		$this->assertFalse($returnValue, "Memcache::get (negative)");
	}
	
}


class Disk_Greater_than_Memory_TestCase_Full extends Disk_Greater_than_Memory_TestCase
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
