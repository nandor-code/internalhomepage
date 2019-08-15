<?php
header("Content-Type: application/json");

$set = $_GET['setstate'];

if( isset($set) )
{
	$s = file_put_contents( "/tmp/house_music", $set);

	$response = array(
   	     'success' => $s,
	    );
}
else
{
	$s = file_get_contents( "/tmp/house_music");

	$response = array(
   	     'status' => $s == 1 ? true : false,
	    );

}

echo json_encode($response);
?>
