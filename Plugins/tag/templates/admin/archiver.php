<h1><?php _e('Plugins:Tag archiver')?></h1>
<div class="yui-g">
  <div class="yui-u first">
    <h2>Currently active</h2>
    <ul>
    <?php foreach($nonarc as $tag):?>
      <li><?php echo $tag?></li>
    <?php endforeach?>  
  </div>
  <div class="yui-u"> 
    <h2>Currently archived</h2>
    <ul>
    <?php foreach($arc as $tag):?>
      <li><?php echo $tag?></li>
    <?php endforeach?>
    </ul>
  </div>
</div>    