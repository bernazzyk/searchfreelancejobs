<?php
require_once( 'common.php' );
$select = db( )->query("SELECT * FROM platforms");

$records = db( )->fetchAll( $select );
var_dump($records);

?>