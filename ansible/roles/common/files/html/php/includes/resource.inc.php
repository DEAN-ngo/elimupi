<?php
if (isset($_GET['resource'])) {
    $resource = $_GET['resource'];    
} else {
    $resource = "wikipedia";
}

if ($resource == "wikipedia"){
    $resourceURL = "http://wiki.elimupi.online";
    $resourceTitle = _("Wikipedia");
    $pageTitle = _("DEAN - ElimuPi - Wikipedia");
} elseif ($resource == "khan") {
    $resourceURL = "http://kolibri.elimupi.online:8080";
    $resourceTitle = _("Khan Academy");
    $pageTitle = _("DEAN - ElimuPi - Khan Academy");
} elseif ($resource == "moodle") {
    $resourceURL = "http://moodle.elimupi.online";
    $resourceTitle = _("Elimu Online");
    $pageTitle = _("DEAN - ElimuPi - Elimu Online");
}
elseif ($resource == "elimuPi"){
    $resourceURL = "admin/";
    $resourceTitle = _("Educational Materials");
    $pageTitle = _("Material");
}

?>