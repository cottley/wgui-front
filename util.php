<?php

  require_once('lang/lang.php');
   
  // create a log channel
  $log = new \Monolog\Logger('wgui-front');
  $log->pushHandler(new \Monolog\Handler\StreamHandler(getConfig('wguiauth.logger.path'), getConfig('wguiauth.logger.level')));

  $dbpath = getConfig('wguiauth.path.to.sqlite.db');
  
  $errors = array();
  $warnings = array();
  $successes = array();

function userError($code, $message) {
  global $errors;
  array_push($errors, $code.': '.t($message));	
	
}

function userWarn($code, $message) {
  global $warnings;
  array_push($warnings, $code.": ".t($message));	
}

function userSuccess($message) {
  global $successes;
  array_push($successes, t($message));	
}

function _getDB() {
  global $dbpath;
  $result = \ParagonIE\EasyDB\Factory::fromArray([
    'sqlite:'.$dbpath
  ]);
  return $result;
}
  
function initializeDBIfNotExists($dbpath) {

  try {
	
    $db = _getDB();
	
	$accountsExists = $db->cell("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name=?", "accounts");
	
	if ($accountsExists == 0) {
	
     userWarn("WARN001", "Database initialized, please login as admin with password admin and change the admin password immediately.");
	
	 $db->run('CREATE TABLE accounts (username text PRIMARY KEY, password text not null, salt text not null, email text, given_name text, surname text, isadmin text)');
	 
	 $db->run('CREATE TABLE usersessions(username text PRIMARY KEY, sessionid text)');
	 
     $adminKey = \Defuse\Crypto\Key::createNewRandomKey();
	 $adminKeyStr = $adminKey->saveToAsciiSafeString();
     $adminPassword = \ParagonIE\PasswordLock\PasswordLock::hashAndEncrypt('admin'.$adminKeyStr, $adminKey);

     $db->insert('accounts', [
	   'username' => 'admin',
	   'password' => $adminPassword,
	   'salt' => $adminKeyStr,
	   'given_name' => 'Administrator',
	   'isadmin' => 'Y'
	   ]);
	   
	} 
	
	
	
  } catch (\ParagonIE\EasyDB\Exception\ConstructorFailed $e) {
    global $log;
	//$log->warning('Foo');
    $log->error('ERR001: Could not access sqlite database at '.$dbpath);
	
	userError('ERR001', 'Unable to connect to database, login may not function as expected.');
  }

}


initializeDBIfNotExists($dbpath);

?>