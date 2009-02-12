<?php if ($username): ?>
<div class="logout"><a href="<?php echo getUrl('user/logout')?>">Déconnexion <?php echo $username?></a></div>
<?php endif ?>
<h3><a href="<?php echo ROOT_ADMIN_URL.ROOT_ADMIN_SCRIPT ?>"><?php echo $this->getOption('adminTitle')?></a></h3>
<?php echo $this->output['choosetable']?>