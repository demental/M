<?php $f=$this->rf($form)?>

<?php 
echo $f['javascript']?>
<form<?php echo $f['attributes']?>><?php echo $f['hidden']?>
	<?php foreach($f['sections'] as $section):?>
	<?php foreach($section['elements'] as $k=>$element):?>
        <?php if ($element['style']):?>
         	<?php $this->i('formelements/'.$element['style'].'.php',array('element'=>$element))?>
		<?php elseif($element['type']=='checkbox'):?>
				<label style="border-bottom:1px solid"><?php echo $element['html'].$element['label']?></label>
        <?php elseif($element['type']=='group'):?>
            <div style="font-size:90%;display:inline">
            <?php foreach ($element['elements'] as $selt):?>
    		<?php echo $selt['label']?>
            <?php echo $selt['html']?>
    		<?php if ($selt['label_unit']) echo $selt['label_unit']?>        

            <?php endforeach?>
            </div>
        <?php else:?>
		<?php echo $element['label']?>
        <?php echo $element['html']?>
        <?php endif?>
		<?php if ($element['label_unit']) echo $element['label_unit']?>        

				<?php if (!empty($element['error'])):?>
				<span class="formError"><?php echo $element['error']?></span>
				<?php endif?>
				<?php if (!empty($element['label_tip'])):?>
        <a class="tip" href="javascript:void(0);" onmouseover="return overlib('<?php echo addslashes($element['label_tip'])?>', CAPTION, '<?php echo $element['label']?>', FGCOLOR, '#aaa', BGCOLOR, '#335', BORDER, 1, CAPTIONFONT, 'Garamond', TEXTFONT, 'Arial', TEXTSIZE, 2)" onmouseout="return nd();"><img src="images/icons/help.png"/></a>
				<?php endif?>

    <?php endforeach?>
<?php endforeach?>
</form>