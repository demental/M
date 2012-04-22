<?php if($data):?>
<h4>Données connexes</h4>
<ul>
<?php foreach($data as $arec):?>
  <li><a href="<?php echo $arec['link']?>"><?php echo $arec['nb']?> <?php echo $arec['tablename']?></a>
    <?php if($arec['add']):?>
      <a href="<?php echo M_Office_Util::getQueryParams(array('module'=>$arec['table'],'addRecord'=>1,'filterField'=>$arec['linkField'],'filterValue'=>$do->{$arec['field']}),array('record','__record_ref'))?>">[+]</a>
    <?php endif?>  
    </li>
<?php endforeach?>
</ul>
<?php endif?>