<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
    <base href="<?php echo SITE_URL?>" />
	  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	  <title><?php echo $this->getOption('adminTitle') ?></title>

  	<link rel="stylesheet" type="text/css" media="screen" href="styleforms.css" />
  	<link rel="stylesheet" type="text/css" media="screen" href="style_admin.css" />
	  <script type="text/javascript" src="js/tools.js"></script>
		<script type="text/javascript" src="js/prototype/prototype.js"></script>
		<script type="text/javascript" src="js/prototype_extended.js"></script>
				<script type="text/javascript" src="js/prototype/scriptaculous.js"></script>
    <script type="text/javascript" src="js/overlib/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>
		<script type="text/javascript">
		<?php
		if(is_array($this->output['javascript'])){
				foreach($this->output['javascript'] as $inst) {
					echo $inst."\n";
				}
		}
		if(is_array($this->output['onLoad'])){
			echo 'window.onload=function() {
				';
			foreach($this->output['onLoad'] as $inst){
				echo $inst."\n";
			}
			echo '}
			';
		}
		if(is_array($this->output['onbeforeunLoad'])){
			echo 'window.onbeforeunload=function() {
				';
			foreach($this->output['onbeforeunLoad'] as $inst){
				echo $inst."\n";
			}
			echo '}
			';
		}
		if(is_array($this->output['onunLoad'])){
			echo 'window.onunload=function() {
				';
			foreach($this->output['onunLoad'] as $inst){
				echo $inst."\n";
			}
			echo '}
			';
		}		
?>
		</script>
	</head>
	<body>
	  <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
      <div id="indicator" style="position:absolute; left:50%;top:50%;display:none;z-index:1000;background:#fff;border:1px solid #000">
        <img src="/images/indicator.gif" />Requête en cours...
      </div>
		<div id="admincontainercompact">
			<?php if(@is_array($this->output['messages']) && count($this->output['messages'])>0): ?>
			<div id="messagePanel">
				<div class="messageContent">
					<ul>
						<?php foreach ($this->output['messages'] as $message): ?>
						<li><?php echo $message[0]?></li>
						<?php endforeach ?>
					</ul>
				</div>
				<div class="messageFooter">
					<a href="javascript:void(0)" onclick="Element.hide('messagePanel')">Fermer</a>
				</div>
			</div>
			<?php endif ?>
		   <?php echo $this->output['showtable'].
		    					$this->output['editrecord'].
		    					$this->output['addrecord'].
		    					$this->output['deleterecords'].
		    					$this->output['actions'].
							(!empty($this->output['frontendhome'])?'<div style="margin:40px">'.$this->output['frontendhome'].'</div>':'')
				?>
			</div>
		</div>
	</body>
</html>