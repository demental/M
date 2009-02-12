<div id="messagePanel" style="z-index:1001">
	<div class="messageContent">
		<ul>
			<?php foreach ($messages as $message): ?>
			<li><?php echo $message[0]?></li>
			<?php endforeach ?>
		</ul>
	</div>
	<div class="messageFooter">
		<a href="javascript:void(0)" id="closepanel">Fermer</a>
	</div>
</div>
