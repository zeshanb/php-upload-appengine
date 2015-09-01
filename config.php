<?php

//config
$storageBucketName = "";//enter your unique bucket-name. differen't from all the other bucket names on cloud storage

$dbHost = ":/cloudsql/project-id-name:sql-server-name"; //e.g. ":/cloudsql/project-id-name:sql-server-name"
$dbUser = "root";
$dbPass = ''; //can be left blank when connecting within AppEngine Project
$dbDatabaseName = "myfiles"; //database name
$dbTableName = "myfiles"; //table name

//"Start the bubble machine!"
DB::connect($dbHost, $dbUser, $dbPass, $dbDatabaseName);

?>