<?php
// Helper functions

function link_to($content, $url, $options = array())
{
  return '<a href="'.$url.'">'.$content.'</a>';
}

function elink_to($content, $url, $options = array())
{
  echo link_to($content, $url, $options);
}
