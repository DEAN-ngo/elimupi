<?php
$output=null;
$retval=null;
exec("sudo /home/amicah/Documents/DEAN/html/apiconnect/copyscript.sh", $output,$retval);
echo "Returned with status $retval and output:\n";
print_r($output);

?>
