<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
    <base href="<?php echo SITE_URL?>" />
	  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	  <title><?php echo $adminTitle ?></title>

  	<link rel="stylesheet" type="text/css" media="screen" href="css/styleforms.css" />
  	<link rel="stylesheet" type="text/css" media="screen" href="css/style_admin.css" />
  	<link rel="stylesheet" type="text/css" media="screen" href="css/livesearch.css" />
    <?php
    foreach(Mtpl::getCSS() as $i) {
        echo '<link rel="stylesheet" href="/css/'.$i['name'].'.css" media="'.$i['media'].'"></script>
        ';
    }
    ?>

    <?php
    foreach(Mtpl::getJS() as $i) {
        echo '<script type="text/javascript" src="'.$jsdir.$i.'.js"></script>
        ';
    }
    ?>

	<script type="text/javascript">
	<?php
	foreach(Mtpl::getJSinline() as $inst) {
		echo $inst."\n";
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
             $("#chooseTable input").livesearch({url:'<?php echo ROOT_ADMIN_URL.ROOT_ADMIN_SCRIPT.'?livesearch=1'?>',minchar:4});
			$("#closepanel").click(function(){$("#messagePanel").hide("fast")}).bind('keyUp',function() {$("#messagePanel").hide("fast")});
            <?php
			foreach(Mtpl::getJSinline('ready') as $inst){
				echo $inst."\n";
			}
            ?>
    });
    window.onbeforeunload=function() {
            <?php
			foreach(Mtpl::getJSinline('beforeunload') as $inst){
				echo $inst."\n";
			}
			?>
	}

    $(window).unload(function() {
            <?php
			foreach(Mtpl::getJSinline('unload') as $inst){
				echo $inst."\n";
			}
			?>
	});
		</script>
	</head>
	<body>
	  <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
      <div id="indicator" style="position:absolute; left:50%;top:50%;display:none;z-index:1000;background:#fff;border:1px solid #000">
        <img src="images/indicator.gif" />Requête en cours...
      </div>
<?php	if($auth){	
		echo '
		<div id="loginContainer">
		'.$auth.'
		</div>
	</body>
</html>';
				return;
			}
?>
		<div id="admincontainer">
			<div id="chooseTable">
				<?php if ($logout): ?>
				<div class="logout"><?php echo $logout?></div>
				<?php endif ?>
				<h3><a href="<?php echo ROOT_ADMIN_URL.ROOT_ADMIN_SCRIPT ?>"><?php echo $adminTitle?></a></h3>
        <?php if ($showlivesearch):?>
			    <input type="search" size="40"/>
          <?php endif?>
<br /><br /><br />
    
			    <?php echo $choosetable?>
			</div>
			<?php
			$menu='
	        <div class="actionMenu">
	        <ul>';
			if(is_array($actionMenu)){
	        foreach($actionMenu as $k){
				$menu.='
	        	<li>'.$k.'</li>';
			}
		}
	        $menu.='</ul>
	        </div>';
			echo $menu;
			?>
			<div id="Controller">
				<div id="sideinfos">
					<?php if(!empty($related)): ?>
					<div id="related">
						<?php echo $related?>
						<br style="clear:both" />
						<?php if(is_array($ajaxFrom['before'])):?>
						<?php foreach($ajaxFrom['before'] as $list):?>
						    <?php echo $list?>
						<?php endforeach?>
						<?php endif?>
						<br style="clear:both" />
					</div>
					<?php endif ?>
					<?php if($relatedaction): ?>
						<div id="relatedaction">
							<h4>Actions :</h4>
							<ul>
								<?php foreach ($relatedaction as $k): ?>
								<li><?php echo $k?></li>
								<?php endforeach ?>
							</ul>
		        	<br style="clear:both" />
						</div>
					<?php endif ?>
					<br style="clear:both" />
				</div>
		   <?php echo $showtable.
		    					$editrecord.
		    					$addrecord.
		    					$deleterecords.
		    					$actions.
							(!empty($frontendhome)?'<div style="margin:40px">'.$frontendhome.'</div>':'')
				?>
			</div>
			<div id="relatedafter">
			    <br style="clear:both" />
				<?php if(is_array($ajaxFrom['after'])):?>
				<?php foreach($ajaxFrom['after'] as $list):?>
				    <?php echo $list?>
				<?php endforeach?>
				<?php endif?>
			</div>	
			<?php echo $menu ?>
	    <div id="bottom">
        <?php $this->i('footer',array('regenerate'=>$regenerate,'module'=>$currentmodule))?>
      </div>
		</div>
		<?php if(@is_array($messages) && count($messages)>0): ?>
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
		<?php endif ?>
	</body>
</html>