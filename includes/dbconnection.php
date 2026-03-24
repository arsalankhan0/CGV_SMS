<?php 
// DB credentials.
define('DB_HOST','localhost');
define('DB_USER','u311638402_tibetan');
define('DB_PASS','Tibetan@786');
define('DB_NAME','u311638402_studentmsdb');
// Establish database connection.
try
{
$dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER, DB_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
}
catch (PDOException $e)
{
exit("Error: " . $e->getMessage());
}
?>