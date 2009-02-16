<?php

define('PROJECT_NAME','{$PROJECT_NAME}');

define('SITE_URL','{$SITE_URL}');
define('WEB_FOLDER','{$DOC_ROOT}');

define('MODE','{$MODE}');

// =================================================
// = Additional paths specific to host             =
// =================================================
{if $PEAR_PATH}
$paths[]='{$PEAR_PATH}';
{/if}

define('DB_URI','{$DB_URI}');
