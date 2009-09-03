<script type="text/javascript">
$(function(){
  $('#next').hide();
  resend = function(){
    $('#redirectform').submit();
  }
    setTimeout(resend,0);
});
</script>
<?php $f = $this->rf($redirectform,'static')?>
<p>Vous allez être redirigé. Si la page ne se recharge pas cliquez sur "continuer".</p>
<form <?php echo $f['attributes']?>>
  <?php echo $f['hidden']?>
<input type="submit" name="__submit__" id="next" value="Continuer" />
</form>