<?php

/*
 * @todo: $modx->config['rb_webuser'] && $_SESSION['webValidated']
 * @todo: $modx->config['denyExtensionRename']
 * @todo: test $modx->config['showHiddenFiles']
 * 
 * */

error_reporting(0); // Set E_ALL for debugging

// Init MODX
require '../modx/init_modx.php';

if($fileMgrDirectory == NULL) {
	$error = array('error'=>'No type set');
	echo json_encode($error);
	exit;
}

// load composer autoload before load elFinder autoload If you need composer
//require './vendor/autoload.php';

// elFinder autoload
require './autoload.php';
// ===============================================

// Enable FTP connector netmount
elFinder::$netDrivers['ftp'] = 'FTP';
// ===============================================

/**
 * # Dropbox volume driver need `composer require dropbox-php/dropbox-php:dev-master@dev`
 *  OR "dropbox-php's Dropbox" and "PHP OAuth extension" or "PEAR's HTTP_OAUTH package"
 * * dropbox-php: http://www.dropbox-php.com/
 * * PHP OAuth extension: http://pecl.php.net/package/oauth
 * * PEAR's HTTP_OAUTH package: http://pear.php.net/package/http_oauth
 *  * HTTP_OAUTH package require HTTP_Request2 and Net_URL2
 */
// // Required for Dropbox.com connector support
// // On composer
// elFinder::$netDrivers['dropbox'] = 'Dropbox';
// // OR on pear
// include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeDropbox.class.php';

// // Dropbox driver need next two settings. You can get at https://www.dropbox.com/developers
// define('ELFINDER_DROPBOX_CONSUMERKEY',    '');
// define('ELFINDER_DROPBOX_CONSUMERSECRET', '');
// define('ELFINDER_DROPBOX_META_CACHE_PATH',''); // optional for `options['metaCachePath']`
// ===============================================

// // Required for Google Drive network mount
// // Installation by composer
// // `composer require nao-pon/flysystem-google-drive:~1.1 nao-pon/elfinder-flysystem-driver-ext`
// // Enable network mount
// elFinder::$netDrivers['googledrive'] = 'FlysystemGoogleDriveNetmount';
// // GoogleDrive Netmount driver need next two settings. You can get at https://console.developers.google.com
// // AND reuire regist redirect url to "YOUR_CONNECTOR_URL?cmd=netmount&protocol=googledrive&host=1"
// define('ELFINDER_GOOGLEDRIVE_CLIENTID',     '');
// define('ELFINDER_GOOGLEDRIVE_CLIENTSECRET', '');
// ===============================================

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function access($attr, $path, $data, $volume) {
	global $modx;
	if($modx->config['showHiddenFiles']) return true;
	
	return strpos(basename($path), '.') === 0       // if file/folder begins with '.' (dot)
		? !($attr == 'read' || $attr == 'write')    // set read+write to false, other (locked+hidden) set to true
		:  null;                                    // else elFinder decide it itself
}

function convertToRegex($arrayString) {
	$string = is_array($arrayString) ? join('|',$arrayString) : $arrayString;
	$string = str_replace(' ','',$string);
	$string = str_replace(',','|',$string);
	return $string;
}

function callback_fileHandler($cmd, $result, $args, $elfinder) {
	global $modx;

	$files = $result['added'];
	
	switch($cmd) {
		default:
			// Trigger Trans-Alias
			if($modx->config['clean_uploaded_filename']) {
				foreach ($files as $i => $file) {
					$filename = $file['name'];
					$sanitized = $modx->stripAlias($filename);
					if (strcmp($filename, $sanitized) != 0) {
						$arg = array('target' => $file['hash'], 'name' => $sanitized);
						$elfinder->exec('rename', $arg);
					}
				}
			}
	}
	
	return true;
}

// Documentation for connector options:
// https://github.com/Studio-42/elFinder/wiki/Connector-configuration-options
$opts = array(
	'debug' => true,	// @todo: remove
	
	'bind' => array(
		// Trigger Trans-Alias
		'mkdir mkfile rename duplicate upload rm paste' => 'callback_fileHandler'
	),
	
	'roots' => array(
		array(
			'driver'        => 'LocalFileSystem',           // driver for accessing file system (REQUIRED)
			'path'          => $modx->config['rb_base_dir'].$fileMgrDirectory,// path to files (REQUIRED)
			'URL'           => (!$modx->config['strip_image_paths'] ? MODX_SITE_URL : '').$modx->config['rb_base_url'].$fileMgrDirectory, // URL to files (REQUIRED)

			'mimeDetect' => 'internal',
			'uploadDeny'    => array('all'),                // All Mimetypes not allowed to upload
			'uploadAllow'   => array('image', 'text/plain'),// Mimetype `image` and `text/plain` allowed to upload
			'uploadOrder'   => array('deny', 'allow'),      // allowed Mimetype `image` and `text/plain` only
			
			'maxWidth'=>$modx->config['maxImageWidth'],
			'maxHeight'=>$modx->config['maxImageHeight'],
			'quality'=>$modx->config['jpegQuality'],
			'preserveExif'=>true,
			
			'uploadMaxSize'=>$modx->config['upload_maxsize'],
			'fileMode' => $modx->config['new_file_permissions'],
			'dirMode' => $modx->config['new_folder_permissions'],
			'tmbSize'=>$modx->config['thumbWidth'],	// Only 1 parameter, thumbnails are square
			'tmbPath'=>$base_path.'assets/'.$modx->config['thumbsDir'].'/',
			'tmbURL'=>'/assets/'.$modx->config['thumbsDir'].'/',
			'tmbBgColor' => 'transparent',
			'accessControl' => 'access', // disable and hide dot starting files (OPTIONAL)
			'attributes' => array(
				array( 
					'pattern' => '/\.('.convertToRegex($blockedExtensions).')$/i',
					'read'   => false,
					'write'  => false,
					'locked' => true,
					'hidden' => true
				),
				array( 
					'pattern' => '/\.('.convertToRegex($allowedExtensions).')$/i',
					'read'   => true,
					'write'  => true,
					'locked' => false,
					'hidden' => false
				)
			)
		)
	)
);

// run elFinder
$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

?>