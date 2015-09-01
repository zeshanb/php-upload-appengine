<?php
	////////////////////////////////////////////////////////////////////////////
	// An example of using the db_mysql functions.
	// Written by Graham Trott (gt@pobox.com).
	// For unrestricted use.

	// This example is intended to run from the command line.
	// It builds a small database containing three records.
	// You should see the following console output:

	//		Connected to database.
	//		Table created.
	//		Barack Obama is president of The United States.
	//		Silvio Berlusconi is prime minister of Italy.
	//		Gordon Broon is prime minister of The United Kingdom.
	//		Gordon Brown is prime minister of The United Kingdom.

	require_once "db_mysql.php";

	// Connect to the database. Provide your own username and password.
	DB::connect("localhost", "username", "password", "example");
	echo "Connected to database.\n";

	// Create a table. If it's already there, drop it.
	if (DB::tableExists("mytable")) DB::dropTable("mytable");
	DB::createTable("mytable", array(
		"name"=>"TEXT",
		"country"=>"TEXT",
		"title"=>"TEXT",
		"prefix"=>"INT"
		));
	echo "Table created.\n";

	// Create a couple of records.
	DB::insert("mytable", array(
		"name"=>"Barack Obama",
		"country"=>"The United States",
		"title"=>"president",
		"prefix"=>1
		));
	DB::insert("mytable", array(
		"name"=>"Gordon Broon",		// deliberate mistake
		"country"=>"The United Kingdom",
		"title"=>"prime minister",
		"prefix"=>44
		));
	DB::insert("mytable", array(
		"name"=>"Silvio Berlusconi",
		"country"=>"Italy",
		"title"=>"prime minister",
		"prefix"=>39
		));

	// Prove it worked.
	$result = DB::select("mytable", "*", "ORDER BY prefix");
	while ($row = DB::fetchRow($result))
	{
		echo $row->name . " is " . $row->title . " of " . $row->country . ".\n";
	}
	DB::freeResult($result);

	// Now fix the deliberate mistake.
	DB::update("mytable", array(
		"name"=>"Gordon Brown"
		), "WHERE prefix=44");

	// Now prove it again. This time, just select the fields we want.
	$row = DB::selectRow("mytable", array("name", "title", "country"), "WHERE prefix=44");
	echo $row->name . " is " . $row->title . " of " . $row->country . ".\n";
?>
