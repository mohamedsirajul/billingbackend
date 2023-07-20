<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type: application/json; charset=UTF-8");

define('HOST', 'ls-b905a3d8faa4200463b96db2e201a7af41d2615c.cjdiwhzr1pzl.ap-south-1.rds.amazonaws.com');
define('USER', 'dbmasteruser');
define('PASS', 'GGrS&2FI.aofUYW!pv8!Cr2rS^Y[UAS.');
define('NAME', 'dbmaster');

$db = new mysqli(HOST ,USER ,PASS ,NAME);
if ($db->connect_errno) {
	die("Database connection error:" . $db->connect_errno);
}
?>