<?php

	$curl = extension_loaded( 'curl' );
	$xml = extension_loaded( 'xml' );

	if( ! $curl )
		echo "Extension CURL not availabele<br>";
	if( ! $xml )
		echo "Extension XML not availabel<br>";
	if( $curl && $xml )
		echo "OK"; 
?>
