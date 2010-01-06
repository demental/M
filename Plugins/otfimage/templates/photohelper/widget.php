<div id="photowidget_<?php echo $record->tableName()?>_<?php echo $record->pk()?>">
<?php $this->startcapture('imagelist')?>
<ul id="imagelist_<?php echo $record->tableName()?>_<?php echo $record->pk()?>" class="otf_imagelist">
  <?php $cnt=0?>
<?php foreach($photos as $photo):?>
<?php $cnt++?>
<li <?php if($photo->ismain):?>class="main"<?php endif?>>

<div class="otf_photocontainer"><img src="<?php echo $photo->atSize(100)?>" /></div>
<a class="otf_deletephoto" href="<?php echo M_Office::URL('otfimage:photohelper/delete',array('record'=>$photo->id))?>"><img src="/images/icons/bin_closed.png" alt="<?php _e('delete')?>" /></a>

<?php if(!$photo->ismain):?>
- <a class="otf_setphotoasmain"  href="<?php echo M_Office::URL('otfimage:photohelper/setasmain',array('record'=>$photo->id))?>">Principale</a>
<?php endif?>
</li>
<?php endforeach?>
</div>
<?php $this->endcapture()?>

<a href="<?php echo M_Office::URL('otfimage:photohelper/widget',array('record'=>$record->pk(),'table'=>$record->tableName()))?>" id="showimagelist_<?php echo $record->tableName()?>_<?php echo $record->pk()?>"><?php _e('%s images',array($cnt))?></a> - 
<a id="addphotolink_<?php echo $record->tableName()?>_<?php echo $record->pk()?>" href="<?php echo M_Office::URL('otfimage:photohelper/add',array('record'=>$record->pk(),'table'=>$record->tableName()))?>"><?php _e('Add image')?></a>
<?php echo $this->getCapture('imagelist')?>

<script type="text/javascript">
$(function(){
  if(typeof(initpheditor)=='function') {
    initpheditor('<?php echo $record->tableName()?>_<?php echo $record->pk()?>');                
  }
})
</script>

</div>