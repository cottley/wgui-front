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

    $allUserRows = $db->run('select username, email, given_name, surname, isadmin from accounts order by username asc');
  
    foreach ($allUserRows as $row) {

	  $userLoggedIn = $db->cell(
        'SELECT count(username) FROM usersessions WHERE username = ?',
        $row['username']
      );
	
	  if ($userLoggedIn == 1) {
        $loggedIn = 'Y';	
	  } else {
	    $loggedIn = 'N';	
	  }

      $result[] = array('username' => $row['username'],
	                  'email' => $row['email'].'',
					  'given_name' => $row['given_name'].'',
					  'surname' => $row['surname'].'',
					  'isadmin' => $row['isadmin'].'',
					  'portalloggedin' => $loggedIn);
    
    }


  }
  
  $output = array();
  
  $output['data'] = $result;

  echo json_encode($output);

?>