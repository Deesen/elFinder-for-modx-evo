<?php if(strtolower($_GET['opener']) == 'tinymce') { ?>
	<script type="text/javascript" src="/assets/plugins/tinymce/tiny_mce/tiny_mce_popup.js"></script>
<?php } ?>

<script>
	function removeSlash(string) {
		if(string.charAt(0) == '/') return string.substr(1);
		return string;
	}
	
	function SetUrl(file) {
		<?php if($modx->config['strip_image_paths']) { ?>
			fileURL = removeSlash(file.url);
		<?php } else { ?>
			fileURL = file.url;
		<?php } ?>
		<?php if(!isset($_GET['opener'])) { ?>
		
			// MODX default
			window.opener.SetUrl(fileURL);
			window.close();
		
		<?php } elseif(strtolower($_GET['opener']) == 'tinymce') { ?>
		
			// TinyMCE 3
			var win = tinyMCEPopup.getWindowArg('window');
			win.document.getElementById(tinyMCEPopup.getWindowArg('input')).value = fileURL;
			if (win.getImageData) win.getImageData();
			if (typeof(win.ImageDialog) != "undefined") {
				if (win.ImageDialog.getImageData)
					win.ImageDialog.getImageData();
				if (win.ImageDialog.showPreviewImage)
					win.ImageDialog.showPreviewImage(fileURL);
			}
			tinyMCEPopup.close();
		
		<?php } elseif(strtolower($_GET['opener']) == 'tinymce4') {?>
		
			// TinyMCE 4
			var win = window.opener ? window.opener : window.parent;
			$(win.document).find('#<?php echo $field; ?>').val(fileURL);
			win.tinyMCE.activeEditor.windowManager.close();
		
		<?php } elseif(strtolower($_GET['opener']) == 'fckeditor') { ?>

			// FCKEditor
			window.opener.SetUrl(fileURL);
			window.close();

		<?php } elseif(strtolower($_GET['opener']) == 'ckeditor') { ?>

			// CKEditor @todo: Finish JS-functions
			this.opener.CKEditor.object.tools.callFunction(this.opener.CKEditor.funcNum, fileURL, '');
			window.close();
		
		<?php } ?>
	}
</script>