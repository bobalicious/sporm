<?php 

exec( "php SimpleExample.php > NewLog.txt");

$sNewLog = file_get_contents( 'NewLog.txt' );
$sOldLog = file_get_contents( 'ReferenceLog.txt' );

if ( $sNewLog !== $sOldLog ) {
	echo( "\r\nThe logs don't match - suggest you take a look...\r\n" );
} else {
	echo( "\r\nThe logs match - GREAT SUCCESS!\r\n" );
}