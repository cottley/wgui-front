<?php

function getAllWGUIAuthConfig() {

  $wguiauthconfig = array();
  $wguiauthconfig['wguiauth.application.language'] = 'en-US';
  $wguiauthconfig['wguiauth.path.to.sqlite.db'] = '/var/www/db/wgui-auth.db';
  $wguiauthconfig['wguiauth.logger.path'] = 'logs/app.log';
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