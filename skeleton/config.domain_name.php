<?php

// Project name - must be the name of the root folder (eg: project)
define('PROJECT_NAME','{$PROJECT_NAME}');
// Site URL - http path to project (eg: http://localhost/www)
define('SITE_URL','{$SITE_URL}');
// Web folder - relative to project (eg : www)
define('WEB_FOLDER','{$DOC_ROOT}');
// Mode - production, development, test
define('MODE','{$MODE}');




// =================================================
// = Additional paths specific to host             =
// =================================================
{if $PEAR_PATH}
$paths[]='{$PEAR_PATH}';
{/if}

define('DB_URI','{$DB_URI}');
