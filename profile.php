<?php 

  session_start();

  if (!isset($_SESSION['loggedin'])) {
    header('Location: index.php');
    exit();
  }

  require_once('util.php');
  
  $loader = new \Twig\Loader\FilesystemLoader(getConfig('wguiauth.twig.template.path'));
  $twig = new \Twig\Environment($loader, []);
  
  $db = _getDB();
  
  $username = $db->cell(
    "SELECT username FROM usersessions WHERE sessionid = ?",
    session_id()
  );
  
  $userData = $db->row(
    "SELECT * FROM accounts WHERE username=?",
    $username
  );
  
  $name = trim($userData['given_name'].' '.$userData['surname']);
  $logoutText = trim(t('Logout').' '.$name);
  
  $renderEdit = false;
  
  if (isset($_GET['mode'])) {
	  
	$mode = $_GET['mode'];
	
	if ($mode == 'edit') {

      $renderEdit = true;

	}
  } 
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	  
	 if (($_POST['currentpassword'] == '') &&
	     ($_POST['newpassword1'] == '') &&
		 ($_POST['newpassword2'] == '')) {
	
        // Not changing password
		
	   $formUsername = trim($_POST['username']);
	   $formEmail = trim($_POST['email']);
	   $formGivenName = trim($_POST['given_name']);
	   $formSurname = trim($_POST['surname']);
		
	   $db->update('accounts', [
         'email' => $formEmail,
		 'given_name' => $formGivenName,
		 'surname' => $formSurname
       ], [
         'username' => $formUsername
       ]);		
		
	   userSuccess('Updated record');
	   
	   $userData = $db->row(
         "SELECT * FROM accounts WHERE username=?",
         $formUsername
       );
		
	   $renderEdit = false;
	
	 } else {
		
        if ($_POST['newpassword1'] != $_POST['newpassword2']) {

           userError('ERR004', 'Unable to change password. New password and repeat password do not match.');		
		
		} else {
		  // Only admin role can set password for another user	
		  
		  // Get current user by session
		  $sessionUser = $username;
		  
		  // Get user from form
		  $formUser = $_POST['username'];
		  
		  // If user names match
		  if ($sessionUser == $formUser) {
			  
			// Check if current password is correct
			$dbKeyStr = $userData['salt'];
		    $dbPassword = $userData['password'];
		    $dbKey = \Defuse\Crypto\Key::LoadFromAsciiSafeString($dbKeyStr);
			$password = $_POST['currentpassword'];
		  
		    if (\ParagonIE\PasswordLock\PasswordLock::decryptAndVerify($password.$dbKeyStr, $dbPassword, $dbKey)) {
		
		       $newMatchingPassword = $_POST['newpassword1'];
		       $newPassword = \ParagonIE\PasswordLock\PasswordLock::hashAndEncrypt($newMatchingPassword.$dbKeyStr, $dbKey);
			   
			   $db->update('accounts', [
                 'password' => $newPassword
               ], [
                 'username' => $formUser
               ]);
			   
			   userSuccess('Password Changed');
			   
			   $formUsername = trim($_POST['username']);
	           $formEmail = trim($_POST['email']);
	           $formGivenName = trim($_POST['given_name']);
	           $formSurname = trim($_POST['surname']);
		
	           $db->update('accounts', [
                 'email' => $formEmail,
		         'given_name' => $formGivenName,
		         'surname' => $formSurname
               ], [
                 'username' => $formUsername
               ]);		
		
               userSuccess('Updated record');
	   
	           $userData = $db->row(
                 "SELECT * FROM accounts WHERE username=?",
                 $formUsername
               );
			   
			   $renderEdit = false;
		
			} else {	
			  userError('ERR006', 'Current password is incorrect. Please try again.');
		    }
			  
		  } else {
		    // If user names don't match check have admin role, if no role error
			$sessionAdminRole = $userData['isadmin'];
			if ($sessionAdminRolw == 'Y') {
				
			  // Don'r need current password for user
			  
			  $formUserData = $db->row(
                "SELECT * FROM accounts WHERE username=?",
                $formUser
              );
			  
			  
			  $dbKeyStr = $formUserData['salt'];
		      $dbKey = \Defuse\Crypto\Key::LoadFromAsciiSafeString($dbKeyStr);
			
			  $newMatchingPassword = $_POST['newpassword1'];
		      $newPassword = \ParagonIE\PasswordLock\PasswordLock::hashAndEncrypt($newMatchingPassword.$dbKeyStr, $dbKey);
			   
			  $db->update('accounts', [
                 'password' => $newPassword
               ], [
                 'username' => $formUser
               ]);
			  
			  $dbKeyStr = $formUserData['salt'];
				
			} else {
				
		      userError('ERR005', 'Unable to change password for another user without the admin role.');		
			
			}
		  }
			
		}
		 
	 }
     
  }
  
  if ($renderEdit) {
	  
      echo $twig->render('profile.edit.html',
              ['wireguard_ui_self_service' => t('Wireguard UI Self Service'),
			   'users' => t('Users'),
			   'settings' => t('Settings'),
			   'profile' => t('Profile'),
               'help' => t('Help'),
			   'about' => t('About'),			   
			   'logout' => $logoutText,
			   'home' => t('Home'),
			   'isadmin' => $userData['isadmin'],
			   'lblProfilePage' => t('Profile Page'),
			   'lblYourAccountDetails' => t('Your account details are below:'),
			   'lblUsername' => t('Username:'),
			   'lblEmail' => t('Email:'),
			   'lblGivenName' => t('Given Name:'),
			   'lblSurname' => t('Surname:'),
			   'lblChangePassword' => t('Change Password:'),
			   'lblEnterCurrentPassword' => t('Enter Current Password:'),
			   'lblEnterNewPassword' => t('Enter New Password:'),
			   'lblRepeatNewPassword' => t('Repeat New Password:'),
			   'lblActions' => t('Actions:'),
			   'lblSubmit' => t('Submit'),
			   'lblCancel' => t('Cancel'),
			   'lblNote' => t('Note: Leave all password fields blank to keep current password.'),
			   'lblIsAdministrator' => t('Is Administrator:'),
			   'dataUsername' => $userData['username'],
			   'dataEmail' => $userData['email'],
			   'dataGivenName' => $userData['given_name'],
			   'dataSurname' => $userData['surname'],
			   'dataIsAdmin' => $userData['isadmin'],
			   'successes' => $successes,
			   'errors' => $errors,
			   'warnings' => $warnings
              ]);
	  
  } else {

    echo $twig->render('profile.html',
              ['wireguard_ui_self_service' => t('Wireguard UI Self Service'),
			   'users' => t('Users'),
			   'settings' => t('Settings'),
			   'profile' => t('Profile'),
			   'help' => t('Help'),
			   'about' => t('About'),
			   'logout' => $logoutText,
			   'home' => t('Home'),
			   'isadmin' => $userData['isadmin'],
			   'lblProfilePage' => t('Profile Page'),
			   'lblYourAccountDetails' => t('Your account details are below:'),
			   'lblUsername' => t('Username:'),
			   'lblEmail' => t('Email:'),
			   'lblGivenName' => t('Given Name:'),
			   'lblSurname' => t('Surname:'),
			   'lblIsAdministrator' => t('Is Administrator:'),
			   'lblActions' => t('Actions:'),
			   'lblEditProfile' => t('Edit Profile'),
			   'lblNote' => t('Note: To change your password you need to edit your profile.'),
			   'dataUsername' => $userData['username'],
			   'dataEmail' => $userData['email'],
			   'dataGivenName' => $userData['given_name'],
			   'dataSurname' => $userData['surname'],
			   'dataIsAdmin' => $userData['isadmin'],
			   'successes' => $successes,
			   'errors' => $errors,
			   'warnings' => $warnings
              ]);
  }
  
?>