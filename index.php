<?php

  require_once('util.php');
  
  $username = '';
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	$username = $_POST['username'];
	$password = $_POST['password'];
	
    if ( trim($username)=='' || $password=='' ) {
	  userError('ERR002', 'Please specify both the username and password fields to login');
    } else {
	  	
	  $db = _getDB();
	  
	  $userExists = $db->cell("SELECT COUNT(*) FROM accounts WHERE username=?", $username);
	  
	  if ($userExists == 0) {
	    userError('ERR003', 'Unable to login. Please check your username and password');
	  } else {
		  
		$userData = $db->row(
          "SELECT * FROM accounts WHERE username=?",
          $username
        );
		
		$dbKeyStr = $userData['salt'];
		$dbPassword = $userData['password'];
		$dbKey = \Defuse\Crypto\Key::LoadFromAsciiSafeString($dbKeyStr);
		  
		if (\ParagonIE\PasswordLock\PasswordLock::decryptAndVerify($password.$dbKeyStr, $dbPassword, $dbKey)) {
		  session_start();
			   
		  $db->delete('usersessions', ['username' => $username]);
				
		  $db->insert('usersessions', [
		      'username' => $username,
			  'sessionid' => session_id()
		    ]);
			   
          $_SESSION['loggedin'] = true;
		  setcookie("wguserfront", $username, time() + (86400 * 30), "/wireguardui/");
		  setcookie("wguser", $username, time() + (86400 * 30), "/wireguardui/");
		
	      header('Location: home.php');
        
        } else {
			
		  userError('ERR003', 'Unable to login. Please check your username and password');
			
		}
		  
	  }
		
    }
	
  }
  
  $loader = new \Twig\Loader\FilesystemLoader(getConfig('wguiauth.twig.template.path'));
  $twig = new \Twig\Environment($loader, []);

  echo $twig->render('index.html',
              ['wireguard_ui_self_service' => t('Wireguard UI Self Service'),
               'username' => t('Username'),
			   'username_value' => $username,
			   'password' => t('Password'),
               'login' => t('Login'),
			   'errors' => $errors,
			   'warnings' => $warnings
              ]);

?>
