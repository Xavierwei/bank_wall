<?php if($code == 1): ?>
	<script>
		window.top.closeUploadPop();
	</script>
<?php elseif($code == 508):?>
	<script>
		window.top.uploadBusy("<?php echo $tmp_file;?>");
	</script>
<?php else:?>
	<script>
		window.top.uploadPopError(<?php echo $code;?>);
	</script>
<?php endif;?>