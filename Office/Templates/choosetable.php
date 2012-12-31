<?php if(empty($_REQUEST['module'])) $activeClass="selected" ?>
<ul>
  <?php foreach($items as $item):?>
    <li class="<?php if($item['submodules']):?>submenu<?php endif?> <?php if($item['expanded']):?>open selected<?php elseif($item['active']):?>selected<?php endif?>">
      <?php $icon = $item['icon'] ? 'icon-'.$item['icon'] : 'icon-th-list' ?>
      <a href="<?php echo $item['url'] ? $item['url']: '#'?>">
        <i class="icon <?php echo $icon ?>"></i> <span><?php echo $item['title']?></span>
      </a>
      <?php if($item['submodules']):?>
        <ul>
          <?php foreach($item['submodules'] as $subitem):?>
            <li class="<?php if($subitem['active']):?>selected<?php endif?>"><a href="<?php echo $subitem['url']?>"><?php echo $subitem['title']?></a></li>
          <?php endforeach?>
        </ul>
        <?php endif?>
    </li>
  <?php endforeach?>
</ul>