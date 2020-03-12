<?php

function getAllWGUIAuthConfig() {

  $wguiauthconfig = array();
  $wguiauthconfig['wguiauth.application.language'] = 'en-US';
  // $wguiauthconfig['wguiauth.db'] = 'sqlite:/var/www/html/db/wgui-auth.db';
  // $wguiauthconfig['wguiauth.db'] = 'pgsql:host=pgsql';
  // $wguiauthconfig['wguiauth.db.user'] = 'postgres';
  $wguiauthconfig['wguiauth.db.password'] = 'password';
  $wguiauthconfig['wguiauth.logger.path'] = 'stdout';
  $wguiauthconfig['wguiauth.logger.level'] = \Monolog\Logger::WARNING;
  $wguiauthconfig['wguiauth.twig.template.path'] = 'templates';
  $wguiauthconfig['wguiauth.wgui.url'] = '/wireguardui/';
  $wguiauthconfig['wguiauth.version'] = '1.0.0';

  return $wguiauthconfig;  
}

function getConfig($var) {

  $wguiauthconfig = getAllWGUIAuthConfig();
  
  return $wguiauthconfig[$var];

}
?>