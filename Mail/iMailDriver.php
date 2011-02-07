<?php

interface iMailDriver {
  public function sendmail($from,$to,$subject,$body,$altbody = null, $options = null, $attachments = null, $html = false);
}