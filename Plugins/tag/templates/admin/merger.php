<h1><?php _e('Plugins:Tag merger')?></h1>
<?php $f = $this->rf($form,'static')?>
<form <?php echo $f['attributes']?>>
  <?php echo $f['hidden']?>
  <div class="yui-g">
    <div class="yui-u first">
      <h3>Tick the tags you want to remove....</h3>
      <?php foreach($f['source'] as $elem):?>
        <?php echo $elem['html']?><?php echo $elem['label']?><br />
      <?php endforeach?>  
    </div>
    <div class="yui-u">
      <h3>... And merge to the following tag :</h3>
      <?php foreach($f['target'] as $elem):?>
        <?php echo $elem['html']?><?php echo $elem['label']?><br />
      <?php endforeach?>
    </div>
  </div>
  <p class="warning">Before submitting please have a careful look at what you did, this cannot be undone !
  <?php echo $f['__submit__']['html']?>
  Tag triggers won't be executed.
  </p>
</form>