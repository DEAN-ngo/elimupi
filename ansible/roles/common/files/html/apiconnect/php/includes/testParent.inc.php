<?php
$pageTitle = "DEAN - ElimuPi - Documents";

include_once("settings.inc.php");

class testParent {
    
    function __construct () {
        $settings = new Settings;
    }
    
    function testing($a){
        return $a;
    }
}
?>