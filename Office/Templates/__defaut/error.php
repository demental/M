<!DOCTYPE html>
<html lang="<?php echo T::getLocale()?>">
	<head>
	  <meta charset="utf-8" />
	  <title><?php echo $adminTitle ?></title>
  	<link rel="stylesheet" type="text/css" media="screen,print" href="/css/reset-fonts-grids.css" />

  	<link rel="stylesheet" type="text/css" media="screen,print" href="/css/styleforms.css" />
  	<link rel="stylesheet" type="text/css" media="screen,print" href="/css/style_admin.css" />
  </head>
  <body>
		<div id="doc3">

    <div id="bd">
      <div class="global_error">
        <h1><?php _e('title.error')?></h1>
        <?php $this->i('__defaut/error.bloc', null, true)?>
        <p><a class="btn btn-large" href="javascript:history.back()"><?php _e('button.back_one_page')?></a>
          <a class="btn btn-warning btn-large" href="<?php echo ROOT_ADMIN_URL?>"><?php _e('button.back_home')?></a></p>
      </div>
</div>
</div>
</body>
</html>
