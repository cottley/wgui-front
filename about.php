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
  
  echo $twig->render('about.html',
              ['wireguard_ui_self_service' => t('Wireguard UI Self Service'),
			   'home' => t('Home'),
			   'users' => t('Users'),
			   'settings' => t('Settings'),
			   'profile' => t('Profile'),
			   'help' => t('Help'),
			   'about' => t('About'),
			   'isadmin' => $userData['isadmin'],
			   'about_wireguard_vpn_ui_front' => t('About WireGuard VPN UI Front'),
			   'version' => t('Version:'),
			   'app_version' => getConfig('wguiauth.version'),
			   'what_is_wgui_front' => t('WireGuard VPN UI Front is a user management front end to provide authentication for WireGuard UI by Embark Studios.'),
			   'wgui_front_plan' => t('It is designed to work with a modified version of WireGuard UI that supports a reverse proxy to a relative directory.'),
			   'wgui_front_click_ui' => t('Click for a modified version of WireGuard UI'),
			   'wgui_front_designed' => t('WireGuard VPN UI Front designed and developed by'),
			   'wgui_front_copyright' => t('Copyright'),
			   'wguiURL' => getConfig('wguiauth.wgui.url'),
			   'logout' => $logoutText,
			   'errors' => $errors,
			   'warnings' => $warnings
              ]);

?>