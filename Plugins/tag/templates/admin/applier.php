<?php if($success):?>
<p class="success"><?php _e('Tag applied to %s records',array($applied))?></p>
<?php else:?>
<?php $f = $this->rf($form,'static')?>
<form <?php echo $f['attributes']?>>
  <table class="formtable" width="100%">
    <tr>
      <td valign="top">SELECT <?php echo $f['distinct']['html']?><strong>DISTINCT</strong> <?php echo $f['table']['html']?>.* FROM <span id="tableclone"></span> </td><td><?php echo $f['clause']['html']?></td></tr>
      <tr><td colspan="2">Apply tag: <?php echo $f['tagname']['html']?></td></tr>
    <tr>
      <td colspan="2"><h2><?php echo $f['__submit__']['html']?></h2></td>
    </tr>
  </table>
</form>
<script type="text/javascript">
$(function(){
  updateclone = function(){
    $('#tableclone').text($('select[name=table]').val());
  }
  $('select[name=table]').bind('change',updateclone);
  updateclone();
})
</script>
<?php endif?>
