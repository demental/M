		<ul>
			<?php foreach ($messages as $message): ?>
			<li><?php echo $message[0]?></li>
			<?php endforeach ?>
		</ul>
<?php $this->startCapture('js')?>

    $('#messagePanel').show();
  $('#closepanel').focus();

<?php $this->endCapture(); Mtpl::addJSinline($this->getCapture('js'))?>
<?php unset($_SESSION['flashmessages'])?>