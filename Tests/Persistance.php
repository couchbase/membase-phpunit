<?php
require_once 'PHPUnit/Framework.php';
require_once 'Include/Utility.php';
	
abstract class Persistance_TestCase extends ZStore_TestCase {

	/**
	* @dataProvider keyValueProvider
	*/
	public function test_Set($testKey, $testValue) {
		$instance = $this->sharedFixture;
		$testFlags = 0;
		// positive set test
		$instance->set($testKey, $testValue);
		$timeset = 0;
		sleep(2);

		for( $dbcount = 0 ; $dbcount < 4 ; $dbcount++)
		{
			$dbh = new PDO('sqlite:'.$GLOBALS['membase_dbpath'].$dbcount.'.sqlite');
			foreach($dbh->query("SELECT v,flags,exptime FROM kv where k like '".$testKey."'") as $row)
			{
				$this->assertEquals($testValue, trim($row["v"]), "sqlite::value");
				$this->assertEquals($testFlags, trim($row["flags"]), "sqlite::flag");
				$this->assertEquals($timeset, $row["exptime"], "sqlite::exptime");
				break 2;
			}
		}
		
		// positive set test
		$testTTL = 30;
		$instance->set($testKey, $testValue, $testFlags, $testTTL);
		$timeset = time() + $testTTL;
		sleep(2);

		for( $dbcount = 0 ; $dbcount < 4 ; $dbcount++)
		{
			$dbh = new PDO('sqlite:'.$GLOBALS['membase_dbpath'].$dbcount.'.sqlite');
			foreach($dbh->query("SELECT exptime FROM kv where k like '".$testKey."'") as $row)
			{
				if ( $row["exptime"] == $timeset or  $row["exptime"] == $timeset - 1)
				{
					$success = TRUE;
				}
				else
				{
					$success = FALSE;
				}
				$this->assertTrue($success, "sqlite::exptime");
				break 2;
			}
		}
	}

	/**
	* @dataProvider keyValueFlagsProvider
	*/
	public function test_Set_Delete($testKey, $testValue, $testFlags) {
		$instance = $this->sharedFixture;

		// positive set test
		$instance->set($testKey, $testValue, $testFlags);
		sleep(2);
		$instance->delete($testKey);
		sleep(1);
		for( $dbcount = 0 ; $dbcount < 4 ; $dbcount++)
		{
			$dbh = new PDO('sqlite:'.$GLOBALS['membase_dbpath'].$dbcount.'.sqlite');
			foreach($dbh->query("SELECT v,flags,exptime FROM kv where k like '".$testKey."'") as $row)
			{
				$this->assertNotEquals($testValue, trim($row["v"]), "sqlite::value");
				break 2;
			}
		}
	}
	
	/**
	* @dataProvider keyProvider
	*/
	public function test_Set_Delete_Multiple_Times($testKey) {
		$instance = $this->sharedFixture;
		
		$testValue = "Test_Value";
		
		// same key
		for( $i = 0 ; $i < 100000 ; $i++)
		{
			$instance->set($testKey, $testValue.$i, 0);
			$instance->delete($testKey);
		}
		sleep(2);
		for( $dbcount = 0 ; $dbcount < 4 ; $dbcount++)
		{
			$dbh = new PDO('sqlite:'.$GLOBALS['membase_dbpath'].$dbcount.'.sqlite');
			foreach($dbh->query("SELECT v,flags,exptime FROM kv where k like '".$testKey."'") as $row)
			{
				$this->assertNotEquals($testValue, trim($row["v"]), "sqlite::value");
			}
		}
		
		// different key
		for( $i = 0 ; $i < 100000 ; $i++)
		{
			$instance->set($testKey.$i, $testValue, 0);
			$instance->delete($testKey.$i);
		}
		sleep(2);
		for( $dbcount = 0 ; $dbcount < 4 ; $dbcount++)
		{
			$dbh = new PDO('sqlite:'.$GLOBALS['membase_dbpath'].$dbcount.'.sqlite');
			foreach($dbh->query("SELECT v,flags,exptime FROM kv where k like '".$testKey."'") as $row)
			{
				$this->assertNotEquals($testValue, trim($row["v"]), "sqlite::value");
			}
		}
	}
	
	/**
	* @dataProvider keyProvider
	*/
	public function test_Set_Delete_Add($testKey) {
		$instance = $this->sharedFixture;
		
		$testValue = "Test_Value";
		$testValue1 = "Test_Value1";
		$testValue2 = "Test_Value2";
		
		$instance->set($testKey, $testValue, 0);
		sleep(2);
		$instance->delete($testKey);
		$instance->add($testKey, $testValue1, 0);
		$instance->set($testKey, $testValue2, 0);
		sleep(1);
		
		for( $dbcount = 0 ; $dbcount < 4 ; $dbcount++)
		{
			$dbh = new PDO('sqlite:'.$GLOBALS['membase_dbpath'].$dbcount.'.sqlite');
			foreach($dbh->query("SELECT v,flags,exptime FROM kv where k like '".$testKey."'") as $row)
			{
				$this->assertNotEquals($testValue, trim($row["v"]), "sqlite::value");
				$this->assertEquals($testValue2, trim($row["v"]), "sqlite::value");

			}
		}
		
	}
	
	
}


class Persistance_TestCase_Quick extends Persistance_TestCase
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

