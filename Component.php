<?php
class Component extends Dispatcher {
    
    function __construct($module, $action='index',$params = null) {
        parent::__construct($module,$action,$params);

    }

    public function display ()
    {
        $this->page->hasLayout(false);

        return parent::display();
    }
}?>