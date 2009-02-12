<?php
class Icons {
    
    public static $iconList = array(
        'delete'=>'bin_closed.png',
        'edit'=>'application_edit.png');
    
    public function getIcon ($value)
    {
        return '<img src="'.SITE_URL.'images/icons/'.Icons::$iconList[$value].'" alt="'.$value.'" border="0" />';
    }
    
}

?>