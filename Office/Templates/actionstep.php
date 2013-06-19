<?php $this->startCapture('js')?>

    $('#next').hide();
  resend = function(){
    $('#redirectform').submit();
  }
    setTimeout(resend,<?php echo $timeout?>);

<?php $this->endCapture(); Mtpl::addJSinline($this->getCapture('js'))?>
<?php $f = $this->rf($redirectform,'static')?>
<h3><?php echo $actionName?> - traitement par lot</h3>
<form <?php echo $f['attributes']?>>
  <?php echo $f['hidden']?>
<div style="width:100%;border:1px solid #000;padding:2px">
  <div style="width:<?php echo $start*100/$total?>%;background:#449">&nbsp;</div>
</div>
<?php echo $start?> / <?php echo $total?>.
</p><?php $remaining=($step>0)?($timeout+2)*(($total-$start)/$step):0; ?>Temps restant estimé : <?php echo round($remaining/60)?> m <?php echo $remaining%60?> s</p>
<?php if($timeout):?>
<p>Cette page se rafraîchit automatiquement toutes les <?php echo $timeout/1000?> secondes...</p>
<?php endif?>
<?php if($remaining>100):?>
  <p>Vous pouvez continuer à travailler en <a href="<?php echo M_Office_Util::getQueryParams(array(),array('glaction','step','__start','doaction','dosingleaction'))?>" target="_blank">cliquant ce lien</a> (ouvre l'office dans une nouvelle fenêtre)</p>
<?php endif?>

<input type="submit" name="__submit__" id="next" value="Suivants" />
</form>