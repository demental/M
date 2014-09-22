<?php if(is_string($output)):?>
  <?php echo $output;return?>
<?php endif?>

<?php if($output['message']):?>
  <p><?php echo $output['message']?></p>
<?php else:?>
  <dl>
  <?php foreach($output as $table => $result):?>
    <dt><?php echo $table?></dt>
    <?php if(count($result) == 0):?>
      <dd><em>No results</em></dd>
    <?php else:?>
      <dd><a href="<?php echo $info['url']?>"><?php echo $info['text'] ?></a></dd>
    <?php endif?>
  <?php endforeach?>
  </dl>
<?php endif?>
