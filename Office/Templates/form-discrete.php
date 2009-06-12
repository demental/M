<?php $f=$this->rf($form)?>
<?php echo $f['javascript']?>
<?php $num=1?>
<form<?php echo $f['attributes']?>><?php echo $f['hidden']?>
	<table align="center" class="formtable" width="100%">              
	<?php foreach($f['sections'] as $section):?>
  <?php $num++?>
	<?php if(!$hideLegend && $section['header']): ?>
	<tr><th colspan="2" <?php if($hidesection):?>class="sectionheader" rel="section_<?php echo $num?>"<?php endif?>><?php echo $section['header']?> </th></tr>
	<?php endif?>
	<?php foreach($section['elements'] as $k=>$element):?>
		<?php if($endform==1):?>
		<?php $endform=0 ?>
		<table class="formtable" cellspacing="4" width="100%">              
        <?php endif?>
        <?php if ($element['style']):?>
         	<?php $this->i('formelements/'.$element['style'].'.php',array('element'=>$element))?>
		<?php elseif($element['type']=='submit' || $element['type']=='reset' && !$f['frozen']):?>
    	<tr>
            <td colspan="2"><h2>
				<div class="submitbutton"><?php echo $element['label_prefix']?><?php echo $element['html']?>
				    <?php echo $element['label_postfix']?><?php if($cancel):?>
				      <?php if($cancelurl):?>
  				      <input type="button" name="cancelbutton" onclick="javascript:top.location.href='<?php echo $cancelurl?>'" value="Annuler" />
              <?php else:?>
				      <input type="button" name="cancelbutton" onclick="javascript:history.go(-1)" value="Annuler" />
				      <?php endif?>
              <?php endif?>
				</div>
				</h2>
			</td>
		</tr>
		<?php elseif($element['type']=='checkbox'):?>
		<tr <?php if($hidesection):?>class="sectionline section_<?php echo $num?>"<?php endif?> id="formrow_<?php echo $element['name']?$element['name']:$k?>">
			<td colspan="2" class="formelement">
        <?php echo $element['html']?><?php echo $element['label']?>
					<?php if($element['required']):?><span class="asterix">*</span><?php endif?>
				<?php if (!empty($element['label_help'])):?>
				<p class="label_help"><?php echo $element['label_help']?></p>
				<?php endif?>
				<?php if (!empty($element['error'])):?>
				<span class="formError"><?php echo $element['error']?></span>
				<?php endif?>
				<?php if (!empty($element['label_help'])):?>
                <div class="label_note"><?php echo $element['label_help']?></div>
				<?php endif?>
		<?php elseif($element['type']=='static' && empty($element['label'])):?>
		<tr>
			<td colspan="2" class="formelement" style="text-align:justify">
			<?php echo $element['html']?>
		<?php else:?>
		<tr <?php if($hidesection):?>class="sectionline section_<?php echo $num?>"<?php endif?> id="formrow_<?php echo $element['name']?$element['name']:$k?>">
			<td align="right" valign="top"  <?php if($nowrap):?>nowrap="nowrap"<?php else:?>width="<?php echo $labelWidth?$labelWidth:'120px'?><?php endif?> <?php if ($element['error']):?>class="formLabelError"<?php else:?>class="formLabel"<?php endif?>><?php echo $element['label']?>
				<?php if ($element['required']):?><span class="asterix">*</span><?php endif?>
				<?php if (!empty($element['label_help'])):?>
				<p class="label_help"><?php echo $element['label_help']?></p>
				<?php endif ?>
			</td>
            <td class="formelement">
				<?php if($element['type']=='group'):?>
					<?php if(!empty($element['error'])):?>
				<span class="formError"><?php echo $element['error']?></span><br />
					<?php endif?>
					<?php foreach ($element['elements'] as $selt):?>
                <table style="display:inline;float:left;margin:0;padding:0;margin-right:2px" cellpadding="0" cellspacing="0">
					<tr><td>
              			<?php echo $selt['html']?>
						<?php if(!empty($selt['label_unit'])):?>
                        <span class="unit">&nbsp;<?php echo $selt['label_unit']?></span> 
						<?php endif?>
						<?php if(!empty($selt['label'])):?>
                     </td></tr>
					 <tr><td>
						<span class="label_note"><?php echo $selt['label']?></span>
							<?php if($selt['required']):?>
						<span class="asterix">*</span>
							<?php endif?>
						<?php endif?>
						<?php if(!empty($selt['error'])):?>
						<span class="formError"><?php echo $selt['error']?></span>
						<?php endif?>
					</td></tr>
                 </table>
				 <?php endforeach?>
			<?php else:?>
				<?php if(!empty($element['error'])):?>
				<span class="formError">
					<?php echo $element['error']?>
				</span><br />
				<?php endif?>
				<?php echo $element['html']?>
			<?php endif?>
			<?php if ($element['type']=='checkbox'):?>
                <?php echo $element['html']?>
				<?php if($element['required']):?><span class="asterix">*</span>
				<?php endif?>
				<?php endif?>
				<?php if(!empty($element['label_unit'])):?>
				<span class="unit"><?php echo $element['label_unit']?></span>
				<?php endif?>
				<?php if(!empty($element['label_note'])):?>
				<div class="label_note"><?php echo $element['label_unit']?></div>
				<?php endif?>
                </td>
            </tr>
<?php endif?>
<?php endforeach?>
<?php endforeach?>
</table>
</form>
<?php if($hidesection):?>
<script type="text/javascript">
  $(function(){
    $('.sectionheader').css({'border-bottom':'1px solid #fff'}).toggle(
    function(){
      $(this).parent().parent().find('.'+$(this).attr('rel')).show('fast');
    },
    function(){
      $(this).parent().parent().find('.'+$(this).attr('rel')).hide('fast');
    }
    );
    $('.sectionline').hide();
  })
</script>
<?php endif?>  