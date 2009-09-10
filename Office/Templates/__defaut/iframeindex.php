<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	  <title><?php echo $adminTitle ?></title>
  	<link rel="stylesheet" type="text/css" media="screen" href="/css/reset-fonts-grids.css" />

  	<link rel="stylesheet" type="text/css" media="screen" href="/css/styleforms.css" />
  	<link rel="stylesheet" type="text/css" media="screen" href="/css/style_admin.css" />
    <?php 
    foreach (Mtpl::getCSS() as $css){
            echo '<link rel="stylesheet" type="text/css" media="'.$css['media'].'" href="/css/'.$css['name'].'.css" />';
    }
    
    foreach (Mtpl::getJS() as $js){
            echo '<script type="text/javascript" src="'.$jsdir.$js.'.js"></script>';
        }
    ?>
    
		<script type="text/javascript">
		<?php
		if(is_array($javascript)){
				foreach($javascript as $inst) {
					echo $inst."\n";
				}
		}
// =====================================================
// = Javascript for dialog window and any ajax request =
// =====================================================
?>
    $(function() {
<?php
foreach(Mtpl::getJSinline('ready') as $inst){
	echo $inst."\n";
}
?>
    });
<?php
		if(is_array($onbeforeunLoad)){
			echo 'window.onbeforeunload=function() {
				';
			foreach($onbeforeunLoad as $inst){
				echo $inst."\n";
			}
			echo '}
			';
		}
		if(is_array($onunLoad)){
			echo '$(window).unload(function() {
				';
			foreach($onunLoad as $inst){
				echo $inst."\n";
			}
			echo '});
			';
		}		
?>
		</script>
	</head>
	<body>
		<div id="doc3">
                <?php $this->i($__action, null, true) ?>
    </div>
	</body>
</html>