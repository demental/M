<?php
// ===============================================
// = For use in future refactoring of Office app =
// ===============================================
class Office_DefaultModule extends Module {
    
    function doExecIndex() {
        $this->assign('__action','home');
	  }    
}