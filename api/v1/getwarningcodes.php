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
 
  $result[] = array('code' => 'WARN001', 'description' => t('Database initialized, please login as admin with password admin and change the admin password immediately.'),
                    'troubleshooting' => t('When first run, the SQLLite database is created and populated with an administrative user (admin) with a default login. After login with the admin user go to the profile page, edit profile and change password so that it is no longer the default.'));  
  
  $output = array();
  
  $output['data'] = $result;

  echo json_encode($output);

?>