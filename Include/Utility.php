<?php 

class Utility {
	
	private static $_testHost;
	private static $_testInstance;
	private static $instancemaster;
	private static $instanceslave;
	private static $instanceslave2;	

	public static function getMaster() {
		$host = $GLOBALS['testHost'];
		if(is_numeric($GLOBALS['mcmux_process'])) {
			ini_set('memcache.proxy_enabled', 1);
			ini_set('memcache.proxy_host', 'unix:///var/run/mcmux/mcmux.sock');
			
			// mcmux 1.0.1.6
			$instancemaster = new Memcache;
			$instancemaster->addServer($host, $GLOBALS['testHostPort']);
			$instancemaster->setServerParams($host, $GLOBALS['testHostPort'], 1, 0);
			// mcmux 1.6.0
	//		$instancemaster = new Memcache;
	//		$instancemaster->addServer($host, $GLOBALS['testHostPort'] , true, 1, 10, 1, 0, NULL, 100, TRUE); // TRUE for Binary protocol, FALSE for Ascii
		}
		else {
			$instancemaster = new Memcache;
			$instancemaster->addServer($host, $GLOBALS['testHostPort']);
			$instancemaster->setServerParams($host, $GLOBALS['testHostPort'], 1, 0);
		}
		return $instancemaster;
	}
  
  	public static function getSlave() {
		$host = $GLOBALS['slaveHost'];
		if(is_numeric($GLOBALS['mcmux_process'])) {
			// mcmux 1.0.1.6
			$instanceslave = new Memcache;
			$instanceslave->addServer($host, $GLOBALS['slaveHostPort']);
			$instanceslave->setServerParams($host, $GLOBALS['slaveHostPort'], 1, 0);
			// mcmux 1.6.0
		
	//		$instanceslave = new Memcache;
	//		$instanceslave->addServer($host, $GLOBALS['slaveHostPort'] , true, 1, 10, 1, 0, NULL, 100, TRUE); // TRUE for Binary protocol, FALSE for Ascii
		}
		else {
			$instanceslave = new Memcache;
			$instanceslave->addServer($host, $GLOBALS['slaveHostPort']);
			$instanceslave->setServerParams($host, $GLOBALS['slaveHostPort'], 1, 0);
		}
		return $instanceslave;
	}	

  	public static function getSlave2() {
		$host = $GLOBALS['slaveHost2'];
		if(is_numeric($GLOBALS['mcmux_process'])) {
			// mcmux 1.0.1.6
			$instanceslave2 = new Memcache;
			$instanceslave2->addServer($host, $GLOBALS['slaveHostPort2']);
			$instanceslave2->setServerParams($host, $GLOBALS['slaveHostPort2'], 1, 0);
			// mcmux 1.6.0
		
	//		$instanceslave2 = new Memcache;
	//		$instanceslav2e->addServer($host, $GLOBALS['slaveHostPort'] , true, 1, 10, 1, 0, NULL, 100, TRUE); // TRUE for Binary protocol, FALSE for Ascii
		}
		else {
			$instanceslave2 = new Memcache;
			$instanceslave2->addServer($host, $GLOBALS['slaveHostPort2']);
			$instanceslave2->setServerParams($host, $GLOBALS['slaveHostPort2'], 1, 0);
		}
		return $instanceslave2;
	}	
	
	public static function getHost() {
		if (!self::$_testHost) {
	        self::$_testHost = $GLOBALS['testHost'];
        }
		return self::$_testHost;
	}
	
	private static $_dataFlags=array();
	private static $_dataKeys=array();
	private static $_dataKeyValues=array();
	private static $_dataKeyValueFlags=array();
	
	public static function prepareData() {
		// all different flags combinations
    	$flags = array(
    		0,
    		MEMCACHE_COMPRESSED, 
    		MEMCACHE_COMPRESSED_LZO,
    		0x20,
    		MEMCACHE_COMPRESSED | 0x20,
    		MEMCACHE_COMPRESSED_LZO | 0x20,
    		
    	); 

    	foreach ($flags as $flag) {
    		
    		// mock textual values
	    	for ($count = 1; $count; --$count) {
	    		$key = uniqid('key_');
	    		$value = self::makeData();
	    		
    			self::$_dataFlags[] = array($flag);
    			self::$_dataKeys[] = array($key);
	    		self::$_dataKeyValues[] = array($key, $value);
	    		self::$_dataKeyValueFlags[] = array($key, $value, $flag);
	    	}
	    	
	    	// mock binary values
	    	for ($count = 1; $count; --$count) {
	    		$key = uniqid('key_');
	    		$value = chr(255) . self::makeData() . chr(254);
	    		
	    		self::$_dataFlags[] = array($flag);
	    		self::$_dataKeys[] = array($key);
	    		self::$_dataKeyValues[] = array($key, $value);
	    		self::$_dataKeyValueFlags[] = array($key, $value, $flag);
	    	}
    	}
	}
	
	public static function makeData() {
		$blob = array();
		
		$fieldCount = mt_rand(10, 20);
		
		for (; $fieldCount; --$fieldCount) {
			$fieldName = uniqid("field_");
			$fieldValue = uniqid("value_");
			
			if (mt_rand() % 3) {
				$count = mt_rand(5, 10);
				$fieldValue = array();
				for (; $count; --$count) {
					$fieldValue[] = mt_rand(0,65536);
				}
			}
			
			$blob[$fieldName] = $fieldValue;
		}
		
		return serialize($blob);
	}

	public function provideKeys() {
    	return self::$_dataKeys;
	}

	public function provideKeyValues() {
    	return self::$_dataKeyValues;
    }

    public function provideFlags() {
    	return self::$_dataFlags;
    }
    
	public function provideKeyValueFlags() {
		return self::$_dataKeyValueFlags;
    }
} 
