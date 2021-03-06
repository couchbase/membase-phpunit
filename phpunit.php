#!/usr/bin/php
<?php
/* PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

if (extension_loaded('xdebug')) {
    ini_set('xdebug.show_exception_trace', 0);
}

if (strpos('@php_bin@', '@php_bin') === 0) {
    set_include_path(dirname(__FILE__) . PATH_SEPARATOR . get_include_path());
}

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

require 'PHPUnit/TextUI/Command.php';

define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');

global $argv, $argc;
if ($argc<3) {
	$GLOBALS['testHost'] = "127.0.0.1";
	$GLOBALS['slaveHost'] = "127.0.0.1";
	$GLOBALS['slaveHost2'] = "127.0.0.1";
	$GLOBALS['testHostPort'] = "11211";
	$GLOBALS['slaveHostPort'] = "11212";
	$GLOBALS['slaveHostPort2'] = "11213";
} else {
	$GLOBALS['testHost'] = $argv[2];
	$GLOBALS['slaveHost'] = $argv[3];
	$GLOBALS['slaveHost2'] = $argv[4];
	$GLOBALS['testHostPort'] = "11211";
	$GLOBALS['slaveHostPort'] = "11211";
	$GLOBALS['slaveHostPort2'] = "11211";
	--$argc;
	array_pop($argv);
}

		// required to test getl function
$GLOBALS['getl_timeout'] = 2 ;

		// This is required to evict the key
$GLOBALS['flushctl_path'] = "Include/management/flushctl";


// Get sqlite DB path
$conn = memcache_pconnect($GLOBALS['testHost'], $GLOBALS['testHostPort']);
$aStats = $conn->getstats();
memcache_close($conn);
$GLOBALS['membase_dbpath'] = $aStats["ep_dbname"]."-";

$GLOBALS['mcmux_process'] = trim(shell_exec('/sbin/pidof mcmux'), "\n");
if(is_numeric($GLOBALS['mcmux_process']))
{
	ini_set('memcache.proxy_enabled', 1);
	ini_set('memcache.proxy_host', 'unix:///var/run/mcmux/mcmux.sock');
}

PHPUnit_TextUI_Command::main($argv);
?>
