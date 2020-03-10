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
    
  $renderCreateUser = false;
  
  if ($userData['isadmin'] == 'N') {
	  
	header('Location: home.php');
	exit();
	
  }
  
  if (isset($_GET['mode'])) {
	  
	$mode = $_GET['mode'];
	
	if ($mode == 'create') {

      $renderCreateUser = true;

	} else if ($mode == 'addadmin') {
		
	  $newadminuser = $_GET['newadminuser'];
	  
	  if ($userData['isadmin'] == 'Y') {
		  
		if ($newadminuser == 'admin') {
			
		  userError('ERR012', 'Unable to modify the admin user.');	
			
		} else {
		  
		  // Ensure username exists
		  $exists = $db->cell("SELECT count(*) FROM accounts WHERE username = ?", $newadminuser);
		 	 
		  if ($exists != 0) {  
		  
		    $db->update('accounts', [
                      'isadmin' => 'Y'
                    ], [
                      'username' => $newadminuser
                    ]);	
					
		    userSuccess('User added to admin role');
		  
		  } else {

            userError('ERR011', 'Unable to add user to admin role. User does not exist.');		
		
		  }
		
		}
		  
	  } else {
		  
		userError('ERR010', 'Unable to add user to admin role. Current user is not an administrator.');	 
		
	  }
		
	} else if ($mode == 'removeadmin') {
		
      $adminuser = $_GET['adminuser'];
	  
	  if ($userData['isadmin'] == 'Y') {
		  
		if ($adminuser == 'admin') {
			
		  userError('ERR012', 'Unable to modify the admin user.');	
			
		} else {
			
		  // Ensure username exists
		  $exists = $db->cell("SELECT count(*) FROM accounts WHERE username = ?", $adminuser);
		 	 
		  if ($exists != 0) {  
		  
		    $db->update('accounts', [
                      'isadmin' => 'N'
                    ], [
                      'username' => $adminuser
                    ]);	
					
		    userSuccess('User removed from admin role');
			
			// If user removed is current user, redirect to home
			if ($adminuser == $username) {
				
			  header('Location: home.php');
			  
			}
		  
		  } else {

            userError('ERR014', 'Unable to remove user from admin role. User does not exist.');		
		
		  }
		  
		}
		
	  } else {
		  
		userError('ERR013', 'Unable to remove user from admin role. Current user is not an administrator.');	 
		
	  }
		
	} else if ($mode == 'impersonate') {
		
	  $user = $_GET['user'];
	  
	  if ($userData['isadmin'] == 'Y') {
		  
		$db->delete('usersessions', ['username' => $username]);
		$db->delete('usersessions', ['sessionid' => session_id()]);
		
		$db->insert('usersessions', [
		      'username' => $user,
			  'sessionid' => session_id()
		]);
			   
        $_SESSION['loggedin'] = true;
		setcookie("wguserfront", $user, time() + (86400 * 30), "/wireguardui/");
		setcookie("wguser", $user, time() + (86400 * 30), "/wireguardui/");
		
	    header('Location: home.php');		  
		  
	  } else {
		  
		userError('ERR015', 'Unable to impersonate user. Current user is not an administrator.');	 
		
	  }
		
	} else if ($mode == 'delete') {
		
	  $existinguser = $_GET['existinguser'];
	  
	  if ($userData['isadmin'] == 'Y') {
		
        if ($existinguser == 'admin') {
			
		  userError('ERR012', 'Unable to modify the admin user.');	
			
		} else {
			
		  // Ensure username exists
		  $exists = $db->cell("SELECT count(*) FROM accounts WHERE username = ?", $existinguser);
		 	 
		  if ($exists != 0) {  
		  
		    $db->delete('accounts', [
                      'username' => $existinguser
                    ]);	
					
		    userSuccess('User deleted');
			
			// If user removed is current user, redirect to home
			if ($existinguser == $username) {
				
			  header('Location: logout.php');
			  
			}
		  
		  } else {

            userError('ERR017', 'Unable to delete user. User does not exist.');		
		
		  }
		  
		}        		
		  
	  } else {
		  
		userError('ERR016', 'Unable to delete user. Current user is not an administrator.');	 
		
	  }
	
    } else if ($mode == 'edit') {
		
	  $renderEditUser = true;
	
	}
	
  }
  
  if ($renderCreateUser) {
	
	$formUsername = '';
	$formEmail = '';	
	$formGivenName = '';
    $formSurname = '';
	$formNewPassword1 = '';
	$formNewPassword2 = '';
	
	
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		
	   $formUsername = trim($_POST['username']);
	   $formEmail = trim($_POST['email']);
	   $formGivenName = trim($_POST['given_name']);
	   $formSurname = trim($_POST['surname']);
	   $formNewPassword1 = $_POST['newpassword1'];
	   $formNewPassword2 = $_POST['newpassword2'];
		
	   if ($formNewPassword1 != $formNewPassword2) {

         userError('ERR009', 'Unable to set password. New password and repeat password do not match.');		
		
	   } else {

         // Ensure username doesn't exist already
		 $exists = $db->cell("SELECT count(*) FROM accounts WHERE username = ?", $formUsername);
		 	 
		 if ($exists != 0) {
			 
		   userError('ERR005', 'Unable to create user. Username exists already and must be unique.');	 
			 
	     } else if ($formUsername == '') { 
		 
		   userError('ERR006', 'Username is a required field and cannot be blank.');
		 
		 } else if ($formGivenName == '') { 
		 
		   userError('ERR007', 'Given Name is a required field and cannot be blank.');
			
		 } else if ($formNewPassword1 == '') {
			
           userError('ERR008', 'Password cannot be blank.');
			
		 } else {
			 
		   $newUserKey = \Defuse\Crypto\Key::createNewRandomKey();
	       $newUserKeyStr = $newUserKey->saveToAsciiSafeString();
           $newUserPassword = \ParagonIE\PasswordLock\PasswordLock::hashAndEncrypt($formNewPassword1.$newUserKeyStr, $newUserKey);

           $db->insert('accounts', [
	         'username' => $formUsername,
			 'email' => $formEmail,
	         'password' => $newUserPassword,
	         'salt' => $newUserKeyStr,
	         'given_name' => $formGivenName,
			 'surname' => $formSurname,
	         'isadmin' => 'N'
	       ]);
		   
		   userSuccess('Successfully added user.');
		
           $formUsername = '';
	       $formEmail = '';
	       $formGivenName = '';
	       $formSurname = '';
	       $formNewPassword1 = '';
	       $formNewPassword2 = '';
		
		 }

       }		   
	  
    }
  
    echo $twig->render('users.create.html',
              ['wireguard_ui_self_service' => t('Wireguard UI Self Service'),
			   'home' => t('Home'),
			   'users' => t('Users'),
			   'settings' => t('Settings'),
			   'profile' => t('Profile'),
			   'help' => t('Help'),
			   'about' => t('About'),
			   'isadmin' => $userData['isadmin'],
			   'lblUsersPage' => t('Users'),
			   'lblNewAccountDetails' => t('New Account Details:'),
               'lblUsername' => t('Username:'),
			   'lblEmail' => t('Email:'),
			   'lblGivenName' => t('Given Name:'),
			   'lblSurname' => t('Surname:'),
			   'lblIsAdministrator' => t('Is Administrator:'),
			   'lblSetPassword' => t('Set Password:'),
			   'lblEnterNewPassword' => t('Enter New Password:'),
			   'lblRepeatNewPassword' => t('Repeat New Password:'),
			   'lblActions' => t('Actions:'),
               'lblCreateUser' => t('Create User'),
               'lblCancel' => t('Cancel'),
               'dataUsername' => $formUsername,
               'dataEmail' => $formEmail,
               'dataGivenName' => $formGivenName,
               'dataSurname' => $formSurname,			   
			   'logout' => $logoutText,
			   'successes' => $successes,
			   'errors' => $errors,
			   'warnings' => $warnings
              ]);

  } else if ($renderEditUser) {
	  
	$formUsername = '';
	$formEmail = '';	
	$formGivenName = '';
    $formSurname = '';
	$formNewPassword1 = '';
	$formNewPassword2 = '';
	
	
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		
	   $formUsername = trim($_POST['username']);
	   $formEmail = trim($_POST['email']);
	   $formGivenName = trim($_POST['given_name']);
	   $formSurname = trim($_POST['surname']);
	   $formNewPassword1 = $_POST['newpassword1'];
	   $formNewPassword2 = $_POST['newpassword2'];
		
	   if ($formNewPassword1 != $formNewPassword2) {

         userError('ERR009', 'Unable to set password. New password and repeat password do not match.');		
		
	   } else {

         // Ensure username exists already
		 $exists = $db->cell("SELECT count(*) FROM accounts WHERE username = ?", $formUsername);
		 	 
		 if ($exists == 0) {
			 
		   userError('ERR018', 'Unable to update user. User does not exist.');	 
			 
	     } else if ($formUsername == '') { 
		 
		   userError('ERR006', 'Username is a required field and cannot be blank.');
		 
		 } else if ($formGivenName == '') { 
		 
		   userError('ERR007', 'Given Name is a required field and cannot be blank.');
			
		 } else if (($formNewPassword1 == '') && ($formNewPassword1 == '')) {
		   
		   // Not changing password
		   $db->update('accounts', [
             'email' => $formEmail,
		     'given_name' => $formGivenName,
		     'surname' => $formSurname
           ], [
             'username' => $formUsername
           ]);		
		
		   userSuccess('Updated record');
           
			
		 } else {
			 
		   $newUserKey = \Defuse\Crypto\Key::createNewRandomKey();
	       $newUserKeyStr = $newUserKey->saveToAsciiSafeString();
           $newUserPassword = \ParagonIE\PasswordLock\PasswordLock::hashAndEncrypt($formNewPassword1.$newUserKeyStr, $newUserKey);

		   // Changing password
		   $db->update('accounts', [
             'email' => $formEmail,
		     'given_name' => $formGivenName,
		     'surname' => $formSurname,
			 'password' => $newUserPassword,
	         'salt' => $newUserKeyStr,
           ], [
             'username' => $formUsername
           ]);		
		
		   userSuccess('Updated record and changed password');
				
		 }

       }		

    } else {
		
	  $existinguser = $_GET['existinguser'];
	  
	  $formUsername = $existinguser;
	  
	  // Ensure username exists already
	  $exists = $db->cell("SELECT count(*) FROM accounts WHERE username = ?", $formUsername);
		 	 
	  if ($exists == 0) {
			 
	    userError('ERR018', 'Unable to update user. User does not exist.');	 
			 
	  } else {
	  
        $existingUserData = $db->row(
          "SELECT * FROM accounts WHERE username=?",
          $formUsername
        );
	  
	    $formEmail = $existingUserData['email'];
	    $formGivenName = $existingUserData['given_name'];
        $formSurname = $existingUserData['surname'];
	  
	  }
    }		

    echo $twig->render('users.edit.html',
              ['wireguard_ui_self_service' => t('Wireguard UI Self Service'),
			   'home' => t('Home'),
			   'users' => t('Users'),
			   'settings' => t('Settings'),
			   'profile' => t('Profile'),
			   'help' => t('Help'),
			   'about' => t('About'),
			   'isadmin' => $userData['isadmin'],
			   'lblUsersPage' => t('Users'),
			   'lblAccountDetails' => t('Account Details:'),
               'lblUsername' => t('Username:'),
			   'lblEmail' => t('Email:'),
			   'lblGivenName' => t('Given Name:'),
			   'lblSurname' => t('Surname:'),
			   'lblIsAdministrator' => t('Is Administrator:'),
			   'lblSetPassword' => t('Set Password:'),
			   'lblEnterNewPassword' => t('Enter New Password:'),
			   'lblRepeatNewPassword' => t('Repeat New Password:'),
			   'lblActions' => t('Actions:'),
               'lblUpdateUser' => t('Update User'),
               'lblCancel' => t('Cancel'),
               'dataUsername' => $formUsername,
               'dataEmail' => $formEmail,
               'dataGivenName' => $formGivenName,
               'dataSurname' => $formSurname,			   
			   'logout' => $logoutText,
			   'successes' => $successes,
			   'errors' => $errors,
			   'warnings' => $warnings
              ]);

	  
  } else {

    echo $twig->render('users.html',
              ['wireguard_ui_self_service' => t('Wireguard UI Self Service'),
			   'home' => t('Home'),
			   'users' => t('Users'),
			   'settings' => t('Settings'),
			   'profile' => t('Profile'),
			   'help' => t('Help'),
			   'about' => t('About'),
			   'isadmin' => $userData['isadmin'],
			   'lblUsersPage' => t('Users'),
               'lblUsername' => t('Username:'),
			   'lblEmail' => t('Email:'),
			   'lblGivenName' => t('Given Name:'),
			   'lblSurname' => t('Surname:'),
			   'lblIsAdministrator' => t('Is Administrator:'),
			   'lblIsLoggedIntoPortal' => t('Is Logged Into Portal'),
			   'lblActions' => t('Actions:'),
			   'lblCreateUser' => t('Create User'),
			   'lblDeleteSelected' => t('Delete Selected'),
			   'lblEdit' => t('Edit'),
			   'lblDelete' => t('Delete'),
			   'lblImpersonate' => t('Impersonate'),
			   'lblRemoveFromAdmin' => t('Remove from Admin'),
			   'lblAddToAdmin' => t('Add to Admin'),
			   'lblEditingUser1' => t('Editing User'),
			   'lblEditingUser2' => t('Editing user'),
			   'lblDeletingUser1' => t('Deleting User'),
			   'lblDeletingUser2' => t('Deleting user'),
			   'lblDeletingUserConfirm' => t('Are you sure? This action cannot be undone.'),
			   'lblImpersonateUser' => t('Impersonate User'),
			   'lblImpersonatingUser' => t('Impersonating user'),
			   'lblAddingUserToAdmin' => t('Adding user to Admin'),
			   'lblRemovingUserFromAdmin' => t('Removing user from Admin'),
			   'logout' => $logoutText,
			   'successes' => $successes,
			   'errors' => $errors,
			   'warnings' => $warnings
              ]);
  }
  
?>