<?php
require 'database.php';
$postdata = file_get_contents('php://input');

if(isset($postdata) && !empty($postdata))
{
	$request = json_decode($postdata,true);
	if (trim($request['name']) == '' || (float)$request['price'] < 0) {
		return http_response_code(400);
	}
	$id = mysqli_real_escape_string($db, (int)$request['id']);
	$name = mysqli_real_escape_string($db, trim($request['name']));
	$price = mysqli_real_escape_string($db, (string)$request['price']);
	$units = mysqli_real_escape_string($db, (string)$request['units']);
	$quantity = mysqli_real_escape_string($db, (string)$request['quantity']);
	$amount = mysqli_real_escape_string($db, (string)$request['amount']);

	$sql = "UPDATE `products` SET `name`='$name',`price`='$price',`units`='$units',`quantity`='$quantity',`amount`='$amount' WHERE id = $id";

	if($db->query($sql))
	{
		http_response_code(204);
	}
	else
	{
		return http_response_code(422);
	}
}