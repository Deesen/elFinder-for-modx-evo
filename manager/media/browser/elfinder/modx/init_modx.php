<?php
include_once(dirname(__FILE__)."/../../../../../assets/cache/siteManager.php");
require_once(dirname(__FILE__).'/../../../../../manager/includes/protect.inc.php');
include_once(dirname(__FILE__).'/../../../../../manager/includes/config.inc.php');
include_once(dirname(__FILE__).'/../../../../../manager/includes/document.parser.class.inc.php');
$modx = new DocumentParser;
$modx->db->connect();
startCMSSession();

if(!isset($_SESSION['mgrValidated'])) { 
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}

define('IN_MANAGER_MODE', "true");
$modx->getSettings();

// GET LANGUAGE AND SET LANG-ATTRIBUTE
include_once MODX_MANAGER_PATH."includes/lang/english.inc.php";
$manager_language = $modx->config['manager_language'];
if(file_exists(MODX_MANAGER_PATH."includes/lang/".$manager_language.".inc.php")) {
	include_once MODX_MANAGER_PATH."includes/lang/".$manager_language.".inc.php";
}
$modx_lang_attribute = isset($modx_lang_attribute) ? $modx_lang_attribute : 'en';

if(!$modx->config['use_browser']) {
	die($_lang['file_browser_disabled_msg']);
}

// Check MODX-permissions
function returnNoPermissionsMessage($role) {
	global $_lang;
	$error = array('error'=>sprintf($_lang['files_management_no_permission'], $role));
	echo json_encode($error);
	exit;
}

$type = isset($_REQUEST['type']) ? strtolower($_REQUEST['type']) : '';

if(($type == 'images' || $type == 'image') && !$modx->hasPermission('file_manager') && !$modx->hasPermission('assets_images')) returnNoPermissionsMessage('assets_images');
if(($type == 'files'  || $type == 'file')  && !$modx->hasPermission('file_manager') && !$modx->hasPermission('assets_files'))  returnNoPermissionsMessage('assets_files');

// Set MODX-parameters
$fileMgrDirectory = NULL;
switch($type) {
	case 'images':
		$fileMgrDirectory  = 'images/';
		$allowedExtensions = $modx->config['upload_images'];
		$blockedExtensions = 'txt|html|php|py|pl|sh|xml';
		break;
	case 'files':
		$fileMgrDirectory  = 'files/';
		$allowedExtensions = $modx->config['upload_files'];
		$blockedExtensions = 'txt|html|php|py|pl|sh|xml';
		break;
	case 'flash':
		$fileMgrDirectory  = 'flash/';
		$allowedExtensions = $modx->config['upload_flash'];
		$blockedExtensions = 'txt|html|php|py|pl|sh|xml';
		break;
	case 'media':
		$fileMgrDirectory  = 'media/';
		$allowedExtensions = $modx->config['upload_media'];
		$blockedExtensions = 'txt|html|php|py|pl|sh|xml';
		break;
};

?>