<?php
class Module_Timeline extends Module {
  public function doExecWidget()
  {
    $cat = $this->getParam('cat');
    $this->assign('cat',$cat);
  }
}
?>