<?php $f=$this->rf($form)?>
<?php echo $f['javascript']?>
<form<?php echo $f['attributes']?>><?php echo $f['hidden']?>
	<?php foreach($f['sections'] as $section):?>
	<?php if(!$hideLegend && $section['header']): ?>
  <h2><?php echo $section['header']?></h2>
	<?php endif?>
	<?php 
	if(!is_array($section['elements'])) {$section['elements']=array();} else {?>
	  <fieldset><?php
	    }
	foreach($section['elements'] as $k=>$element):?>
    <?php switch(true):?>
<?php case ($element['style']):?>
         	<?php $this->i('_formelements/styles/'.$element['style'].'.php',array('element'=>$element))?>
          
		<?php break;case ($element['type']=='submit' || $element['type']=='reset'):?>
        <?php if (!$f['frozen']):?>
				<div class="submitbutton"><?php echo $element['label_prefix']?><?php echo $element['html']?>
				    <?php echo $element['label_postfix']?>
				</div>
        <?php endif?>
		<?php break;case ($element['type']=='checkbox'):?>
    <?php $this->i('_formelements/checkbox',array('elem'=>$element))?>
		<?php break;case ($element['type']=='static' && empty($element['label'])):?>
    <?php $this->i('_formelements/static',array('elem'=>$element))?>
		<?php break;case ($element['type']=='group'):?>
    <?php $this->i('_formelements/group',array('group'=>$element))?>
    <?php break;default:?>
    <?php $this->i('_formelements/element',array('elem'=>$element))?>
    <?php endswitch?>
  <?php endforeach?>
  <?php if(count($section['elements']!=0)):?></fieldset><?php endif?>
<?php endforeach?>  
</table>
</form>