<?php if(isset($url)): ?>
	<script>
		window.top.closeAvatarPop("<?php echo $url?>");
	</script>
<?php else:?>
	<script>
		window.top.uploadPopError(<?php echo $code;?>);
	</script>
<?php endif;?>