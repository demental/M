<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	  <title><?php echo $adminTitle ?></title>
  	<link rel="stylesheet" type="text/css" media="screen,print" href="/css/reset-fonts-grids.css" />

  	<link rel="stylesheet" type="text/css" media="screen,print" href="/css/styleforms.css" />
  	<link rel="stylesheet" type="text/css" media="screen,print" href="/css/style_admin.css" />
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
            $("#loading").ajaxStart(function(){ 
              $('indicator').show().css(
                  'left:'+mouse.x + 'px',
                  'top:'+mouse.y + 'px')
             });
			$("#closepanel").click(function(){$("#messagePanel").hide("fast")});
      say = function(text) {
          $('#messagePanel ul').html('<li>'+text+'</li>');
          $('#messagePanel').show();
          $('#closepanel').focus();
      }
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
	  <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
      <div id="indicator" style="position:absolute; left:50%;top:50%;display:none;z-index:1000;background:#fff;border:1px solid #000">
        <img src="images/indicator.gif" />Requête en cours...
      </div>
		<div id="doc3">
      <div id="hd">
        <div id="messagePanel" style="z-index:1001;display:none">
        	<div class="messageContent">
		    <?php if(is_array($messages) && count($messages)>0):?>
                <?php echo $this->i('dialogbox',array('messages'=>$messages))?>
            <?php endif?>
          	</div>
          	<div class="messageFooter">
          		<a href="javascript:void(0)" id="closepanel">Fermer</a>
          	</div>
          </div>            
			<div id="chooseTable">
			<?php $this->i('header')?>
			  <h3><a href="<?php echo ROOT_ADMIN_URL.ROOT_ADMIN_SCRIPT?>"><?php echo $adminTitle?></a></h3>
                			    <input type="search" size="40"/>
                			    <br /><br /><br />
                			    <?php if($username):?>
                			      <div class="logout">
                			        <?php $this->i('profile')?>
                			      <a href="<?php echo ROOT_ADMIN_URL.ROOT_ADMIN_SCRIPT?>?logout=1"><?echo __('Logout').' '.$username?></a>
                			      </div>
                			    <?php endif?>  
                <?php echo $choosetable?>
			</div>

			<?php
            $menu = $this->i('actionmenu',array('actions'=>$subActions));
			echo $menu;
			?>
			</div>
			<div id="bd" style="clear:both">

			  <div class="yui-b">
                <?php $this->i($__action, null, true) ?>
        </div>
      </div>
			<div id="ft">
        <?php $this->i('footer',array('regenerate'=>$regenerate,'module'=>$currentmodule))?>
			</div>
		</div>
	</body>
</html>