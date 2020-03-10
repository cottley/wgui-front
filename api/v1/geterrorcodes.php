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
 
  $result[] = array('code' => 'ERR001', 'description' => t('Unable to connect to database, login may not function as expected.'),
                    'troubleshooting' => t('There is a configuration issue that is preventing the system from connecting to the system\'s database and prevent the system from working. Contact the administrator.')); 
 
  $result[] = array('code' => 'ERR002', 'description' => t('Please specify both the username and password fields to login'), 
                    'troubleshooting' => t('You must input values for both the username and password.')); 
  
  $result[] = array('code' => 'ERR003', 'description' => t('Unable to login. Please check your username and password'), 
                    'troubleshooting' => t('If the user cannot remember their username and password you can reset it for them as an admin.'));	
					
  $result[] = array('code' => 'ERR004', 'description' => t('Unable to change password. New password and repeat password do not match.'),
                    'troubleshooting' => t('Ensure both the password entries match.')); 
  
  $result[] = array('code' => 'ERR005', 'description' => t('Unable to create user. Username exists already and must be unique.'),
                    'troubleshooting' => t('Specify a different username.')); 
					
  $result[] = array('code' => 'ERR006', 'description' => t('Username is a required field and cannot be blank.'),
                    'troubleshooting' => t('Specify a non blank username.')); 					
										
  $result[] = array('code' => 'ERR007', 'description' => t('Given Name is a required field and cannot be blank.'),
                    'troubleshooting' => t('Specify a non blank given name.')); 

  $result[] = array('code' => 'ERR008', 'description' => t('Password cannot be blank.'),
                    'troubleshooting' => t('Specify a non blank password.')); 
					
  $result[] = array('code' => 'ERR009', 'description' => t('Unable to set password. New password and repeat password do not match.'),
                    'troubleshooting' => t('Ensure both the password entries match.')); 					

  $result[] = array('code' => 'ERR010', 'description' => t('Unable to add user to admin role. Current user is not an administrator.'),
                    'troubleshooting' => t('Ensure current user is a member of the admin role.')); 
					
  $result[] = array('code' => 'ERR011', 'description' => t('Unable to add user to admin role. User does not exist.'),
                    'troubleshooting' => t('Ensure user exists with that username.')); 					
					
  $result[] = array('code' => 'ERR012', 'description' => t('Unable to modify the admin user.'),
                    'troubleshooting' => t('Ensure you are not attempting to modify the admin user role.')); 	

  $result[] = array('code' => 'ERR013', 'description' => t('Unable to remove user from admin role. Current user is not an administrator.'),
                    'troubleshooting' => t('Ensure current user is a member of the admin role.')); 						
			
  $result[] = array('code' => 'ERR014', 'description' => t('Unable to remove user from admin role. User does not exist.'),
                    'troubleshooting' => t('Ensure user exists with that username.')); 
					
  $result[] = array('code' => 'ERR015', 'description' => t('Unable to impersonate user. Current user is not an administrator.'),
                    'troubleshooting' => t('Ensure current user is a member of the admin role.')); 		

  $result[] = array('code' => 'ERR016', 'description' => t('Unable to delete user. Current user is not an administrator.'),
                    'troubleshooting' => t('Ensure current user is a member of the admin role.')); 	

  $result[] = array('code' => 'ERR017', 'description' => t('Unable to delete user. User does not exist.'),
                    'troubleshooting' => t('Ensure user exists with that username.')); 							
					
  $result[] = array('code' => 'ERR018', 'description' => t('Unable to update user. User does not exist.'),
                    'troubleshooting' => t('Ensure user exists with that username.')); 	
					
  $output = array();
  
  $output['data'] = $result;

  echo json_encode($output);

?>