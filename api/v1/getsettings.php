<?php

  session_start();

  if (!isset($_SESSION['loggedin'])) {
    header('Location: ../../index.php');
    exit();
  }

  require_once('../../util.php');

  $result = array();

  $db = _getDB();
  
  $username = $db->cell(
    "SELECT username FROM usersessions WHERE sessionid = ?",
    session_id()
  );
  
  $userData = $db->row(
    "SELECT * FROM accounts WHERE username=?",
    $username
  );
  
  if ($userData['isadmin'] == 'Y') {

    $settingsData = getAllWGUIAuthConfig();
  
    foreach($settingsData as $settingsDataKey => $settingsDataValue) {

      $result[] = array('key' => $settingsDataKey,
	                  'value' => $settingsDataValue);
    
    }


  }
  
  $output = array();
  
  $output['data'] = $result;

  echo json_encode($output);

?>