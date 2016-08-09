<?php
	// Init MODX
	require 'modx/init_modx.php';
	
	$type = isset( $_GET['type'] ) ? $_GET['type'] : '';
	$field = isset( $_GET['field'] ) ? $_GET['field'] : '';
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>elFinder 2.1.14</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2" />

	<!-- jQuery and jQuery UI (REQUIRED) -->
	<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

	<!-- elFinder CSS (REQUIRED) -->
	<link rel="stylesheet" type="text/css" href="css/elfinder.min.css">
	<link rel="stylesheet" type="text/css" href="css/theme.css">

	<!-- elFinder JS (REQUIRED) -->
	<script src="js/elfinder.min.js"></script>

	<!-- GoogleDocs Quicklook plugin for GoogleDrive Volume (OPTIONAL) -->
	<!--<script src="js/extras/quicklook.googledocs.js"></script>-->

	<!-- elFinder translation (OPTIONAL) -->
	<script src="js/i18n/elfinder.<?php echo $modx_lang_attribute ?>.js"></script>

	<?php require 'modx/init_javascript.php'; ?>
	
	<!-- elFinder initialization (REQUIRED) -->
	<script type="text/javascript" charset="utf-8">
		// Documentation for client options:
		// https://github.com/Studio-42/elFinder/wiki/Client-configuration-options

		var elCommands = elFinder.prototype._options.commands;
		
	<?php if($modx->config['denyZipDownload']) { ?>
		var disabled = ['extract', 'archive'];
		$.each(disabled, function(i, cmd) {
			(idx = $.inArray(cmd, elCommands)) !== -1 && elCommands.splice(idx,1);
		});
	<?php }?>
		
		var $window = $(window);
		var $bottomOffset = 18;
		
		$(document).ready(function() {
			
			var $elfinder = $('#elfinder').elfinder({
				lang: '<?php echo $modx_lang_attribute ?>',                // language (OPTIONAL)
				url : 'php/connector.modx.php?type=<?php echo $type; ?>',  // connector URL (REQUIRED)
				getFileCallback: function(file, elf) {
					SetUrl(file);
				},
				commands: elCommands,
				resizable: true,
				height: $window.height() - $bottomOffset
			}).elfinder('instance');
			
			$window.resize(function(){
				myVar = setTimeout(function(){
					var win_height = $window.height() - $bottomOffset;
					if ($elfinder.options.height != win_height) {
						$elfinder.resize('auto', win_height);
					}
				}, 50);
			});
		});
		
	</script>
</head>
<body>

<!-- Element where elFinder will be created (REQUIRED) -->
<div id="elfinder"></div>

</body>
</html>