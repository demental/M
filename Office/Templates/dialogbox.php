		<ul>
			<?php foreach ($messages as $message): ?>
			<li><?php echo $message[0]?></li>
			<?php endforeach ?>
		</ul>
<script type="text/javascript">
$(function() {
  $('#messagePanel').show();
  $('#closepanel').focus();
});
</script>
<?php unset($_SESSION['flashmessages'])?>