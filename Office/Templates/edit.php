<div id="sideinfos">
	<?php if($linkFromTables || $linkToTables): ?>
	<div id="related">
    <?php if($linkFromTables):?>
    <?php $this->i('linkfromtables',array('data'=>$linkFromTables,'do'=>$do))?>
    <?php endif?>
    <?php if($linkToTables):?>
    <?php $this->i('linktotables',array('data'=>$linkToTables,'do'=>$do))?>
    <?php endif?>
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

				<li>
          <?php if($k['url']):?>
            <a href="<?php echo $k['url']?>" <?php echo $k['html']?>><?php echo $k['title']?></a>
          <?php else:?>
            <span class="disabled"><?php echo $k['title']?></span>
          <?php endif?>
        </li>
				<?php endforeach ?>
			</ul>
	            <br style="clear:both" />
		</div>
	<?php endif ?>
	<br style="clear:both" />
</div>
<?php $this->i('editform',array('form'=>$editForm,'do'=>$do,'ajaxFrom'=>$ajaxFrom,'editable'=>$editable))?>
<script type="text/javascript">
  window.currentFocus = { model: "<?php echo $do->tableName()?>", id: "<?php echo $do->pk()?>", to_s: "<?php echo $do->__toString()?>" }
</script>
