<?php
while(!@include 'M/M_Startup.php') {
  fwrite(STDOUT,'
M framework does not seem to be in your include_path.
Please provide M parent path or leave empty to abort : ');
  $path = trim(fgets(STDIN));
  if(empty($path)) die('Aborting project creation
  ');
}
