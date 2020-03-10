<?php
  session_start();
  
  require_once('util.php');
  
  $db = _getDB();
  
  $db->delete('usersessions', ['sessionid' => session_id()]);
  
  //setcookie("wguser", "", time() - 3600, "/wireguardui/");
  setcookie("wguserfront", "anonymous", time() + (86400 * 30), "/wireguardui/");
  
  session_destroy();

  // Redirect to the login page:
  header('Location: index.php');
?>