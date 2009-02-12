<div class="actionMenu">
    <ul>
    <?php
    	if(is_array($actions)){
            foreach($actions as $action){
            	?>
            	<li>
            	<?php echo $action?>
            	</li>
            	<?php
        	}
        }
    ?>
    </ul>
</div>
