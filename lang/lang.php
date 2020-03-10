<?php
  require_once(dirname(__DIR__).'/vendor/autoload.php');
  
  require_once(dirname(__DIR__).'/config.php');
  $configuredLanguage = getConfig('wguiauth.application.language');
  
  $langMessages = (include_once(dirname(__DIR__).'/lang/'.$configuredLanguage.'/messages.php'));
  
  /*
   * Translate the key, which is the original text in English
   * by looking up the key in the language file for the defined locale.
   * If not defined, it will use the English text.
   */
  function t($var) {
	global $langMessages;
	$result = $var;
	if (array_key_exists($var, $langMessages)) {
	  $result = $langMessages[$var];
	}
    return $result;
  }
  
  /*
   * Translate and echo.
   */
  function te($var) {
	echo t($var);  
  }

?>