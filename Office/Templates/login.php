<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	  <title><?php echo $adminTitle ?></title>
  	<link rel="stylesheet" type="text/css" media="screen" href="/css/reset-fonts-grids.css" />

  	<link rel="stylesheet" type="text/css" media="screen" href="/css/styleforms.css" />
  	<link rel="stylesheet" type="text/css" media="screen" href="/css/style_admin.css" />
    <?php foreach (Mtpl::getJS() as $js){
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
            $("#loading").ajaxStart(function(){ 
              $('indicator').show().css(
                  'left:'+mouse.x + 'px',
                  'top:'+mouse.y + 'px')
             });
			$("#closepanel").click(function(){$("#messagePanel").hide("fast")});
<?php
foreach(Mtpl::getJSinline('ready') as $inst){
	echo $inst."\n";
}
?>
    });
		</script>
	</head>
	<body>
		<div id="doc3">
      <div id="hd">
			  <h3><a href="<?php echo ROOT_ADMIN_URL.ROOT_ADMIN_SCRIPT?>"><?php echo $adminTitle?></a></h3>
			</div>			
			<div id="bd">
			  <div class="yui-b">
                <?php $this->i('form-discrete', array('form'=>$loginForm)) ?>
        </div>
      </div>
			<div id="ft">
			</div>
		</div>
	</body>
</html>