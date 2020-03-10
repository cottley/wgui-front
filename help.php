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

  echo $twig->render('help.html',
              ['wireguard_ui_self_service' => t('Wireguard UI Self Service'),
			   'home' => t('Home'),
			   'users' => t('Users'),
			   'settings' => t('Settings'),
			   'profile' => t('Profile'),
			   'help' => t('Help'),
			   'about' => t('About'),
			   'isadmin' => $userData['isadmin'],
			   'lblHelpPage' => t('Help'),
			   'lblWarningCodes' => t('Warning Codes'),
			   'lblCode' => t('Code'),
			   'lblDescription' => t('Description'),
			   'lblTroubleshooting' => t('Troubleshooting'),
			   'logout' => $logoutText,
			   'errors' => $errors,
			   'warnings' => $warnings
              ]);

?>