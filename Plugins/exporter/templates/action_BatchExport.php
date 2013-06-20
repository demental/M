<?php $f = $this->rf($actionform,'static')?>
<h2><?php _e('Report Export')?></h2>
<form <?php echo $f['attributes']?>>
  <?php echo $f['hidden']?>
  <?php _e('Group by')?> : <?php echo $f['groupby']['html']?><br />
  <fieldset><legend><a href="#" id="customshowhide">... <?php _e('or create/load/save a custom Query')?></a></legend>
    <div id="customcontainer">
      SELECT <?php echo $f['fields']['html']?> FROM <?php echo $f['join']['html']?> WHERE <?php echo $f['clause']['html']?><br />
      <?php if($f['store']):?>
        <?php echo $f['store']['html']?><?php _e('Store this query as')?> <?php echo $f['storeas']['html']?><br />
        .. <?php _e('Or load stored query')?> <?php echo $f['stored']['html']?>
      <?php endif?>
    </div>
  </fieldset>
  <h2 class="submit"><?php echo $f['__submit__']['html']?></h2>
</form>
<?php $this->startCapture('js')?>

    $('#customcontainer').hide();
  $('#customshowhide').toggle(function(){$('#customcontainer').show()},function(){$('#customcontainer').hide()});

<?php $this->endCapture(); Mtpl::addJSinline($this->getCapture('js'))?>