<?php
	////////////////////////////////////////////////////////////////////////////
	// This class is a simple wrapper to handle MySQL database functions.
	// One fundamental assumption is made, that all tables have a single
	// auto-incrementing integer primary key.
	// All functions in this package are static; there is no DB object to instantiate.
	//
	// The main benefit of using this class is that all queries use a similar structure,
	// where an entire record is represented as an associative array.
	// This eliminates the chore of matching up quotes and greatly reduces
	// the number of SQL errors.
	//
	// This class was written by Graham Trott (gt@pobox.com) and is for unrestricted use.
	class DB
	{
		////////////////////////////////////////////////////////////////////////////
		// Connect to the database.
		// Arguments:
		// $host the URL of the MySQL host, e.g. 'localhost' or 'mysql.mydomain.com'.
		// $user your MySQL username.
		// $password your MySQL password.
		// $database the database you wish to use.
		public static function connect($host, $user, $password, $database)
		{
			mysql_connect($host, $user, $password)
				or die('Could not connect: '.mysql_error());
			mysql_select_db($database);
		}

		////////////////////////////////////////////////////////////////////////////
		// Create a table from an array of names and field types.
		// Arguments:
		// $name the name of the table.
		// $fields an associative array of fields, e.g.
		//			array(
		//				"name"=>"TEXT",
		//				"email"=>"TEXT",
		//				"age"=>"INT",
		//				"exempt"=>"BOOL",
		//				"status"=>"CHAR(1)"
		//				);
		// A primary integer id field is assumed.
		public static function createTable($name, $fields)
		{
			$sql = "CREATE TABLE $name
			(
				id INT NOT NULL AUTO_INCREMENT,
				PRIMARY KEY(id),";
				$list = NULL;
				foreach ($fields as $key=>$value)
				{
					if ($list) $list .= ", ";
					$list .= " $key $value";
				}
			$sql .= "$list)";
			//echo "$sql<br />";
			mysql_query($sql) or die("Error: ".mysql_error());
		}

		////////////////////////////////////////////////////////////////////////////
		// Drop a table.
		// Arguments:
		// $name the name of the table.
		public static function dropTable($name)
		{
			mysql_query("DROP TABLE $name");
		}

		////////////////////////////////////////////////////////////////////////////
		// Get the column metadata for a table.
		// This can be used to query the structure of a table,
		// but is mainly only used internally.
		// Arguments:
		// $name the name of the table.
		public static function getColumnMetadata($table)
		{
			$result = mysql_query("select * from $table");
			if (!$result) {
				die('Query failed: ' . mysql_error());
			}
			/* get column metadata */
			$columns = array();
			$column = 0;
			while ($column < mysql_num_fields($result))
			{
				$meta = mysql_fetch_field($result, $column);
				$columns[] = array(
					//"blob"=>$meta->blob,
					//"max_length"=>$meta->max_length,
					//"multiple_key"=>$meta->multiple_key,
					"name"=>$meta->name,
					//"not_null"=>$meta->not_null,
					"numeric"=>$meta->numeric,
					//"primary_key"=>$meta->primary_key,
					//"table"=>$meta->table,
					//"type"=>$meta->type,
					//"default"=>$meta->def,
					//"unique_key"=>$meta->unique_key,
					//"unsigned"=>$meta->unsigned,
					//"zerofill"=>$meta->zerofill
					);
				$column++;
			}
			mysql_free_result($result);
			return $columns;
		}

		////////////////////////////////////////////////////////////////////////////
		// Report if a table exists.
		// Arguments:
		// $name the name of the table.
		public static function tableExists($name)
		{
			$tables = array();
			$result = mysql_query("SHOW TABLES LIKE '$name'");
			if (!$result)
			{
				echo "DB Error, could not list tables.<br />";
				echo 'MySQL Error: ' . mysql_error();
				exit;
			}
			$value = ($row = mysql_fetch_row($result));
			mysql_free_result($result);
			return $value;
		}

		////////////////////////////////////////////////////////////////////////////
		// Export a complete table's data.
		// This function writes the entire table to a property list
		// containing a block of lines per record with a blank line between the
		// record blocks. For example:
		//		id=1
		//		name=Barack+Obama
		//		password=%241%24mdN55T9
		//		enable=1
		//		timestamp=1210075624
		//
		//		id=2
		//		name=Gordon+Brown
		//		password=%241%24r4tErsV
		//		enable=0
		//		timestamp=1221073923
		//
		// It uses the table metadata to determine how to format the output.
		// Arguments:
		// $name the name of the table.
		public static function export($table)
		{
			$meta = self::getColumnMetadata($table);
			$fileName = getPath("export/$table.txt");
			$file = fopen($fileName, "w") or die("Can't open file:$fileName<br />");
			$sql = "SELECT * FROM $table ORDER BY id";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_array($result))
			{
				// Iterate the columns
				foreach ($meta as $column)
				{
					$name = $column['name'];
					$numeric = $column['numeric'];
					// Get the data value
					$value = $row[$name];
					if (!$numeric) $value = urlencode($value);
					$s = "$name=$value\n";
					fwrite($file, $s);
				}
				fwrite($file, "\n");
			}
			fclose($file);
		}

		////////////////////////////////////////////////////////////////////////////
		// Import a complete table's data.
		// This method allow you to change the structure of the table
		// and import old data into it.
		// The normal sequence of operations is to export the existing table,
		// then drop the table, then import the data into a newly created table,
		// which may have a different structure. The function will ignore data
		// for fields that no longer exist. You can of course make manual changes
		// to the exported data as its format is simple (described above).
		// Arguments:
		// $name the name of the table.
		public static function import($table)
		{
			$meta = self::getColumnMetadata($table);
			$fileName = getPath("export/$table.txt");
			if (!file_exists($fileName)) return;
			$file = fopen($fileName, "r") or die("Can't open file:$fileName<br />");
			while (!feof($file))
			{
				$read = array();
				while (TRUE)
				{
					$item = trim(fgets($file));
					if (!$item) break;
					$index = strpos($item, "=");
					$name = substr($item, 0, $index);
					$value = substr($item, $index + 1);
					$read[$name] = $value;
				}
				if (!count($read)) return;
				// Now we have a record, but it might be in an old format.
				// So check if all the fields are present.
				$record = array();
				foreach ($meta as $column)
				{
					$name = $column['name'];
					$numeric = $column['numeric'];
					// See if the data is present for this column.
					if (isset($read[$name])) $value = $read[$name];
					else $value = NULL;
					if ($name == "id")
					{
						$id = $value;
						continue;
					}
					if (!$numeric) $value = addslashes(urldecode($value));
					$record[$name] = $value;
				}
				// Now we have all the fields and their data, so write a record.
				// Set the inserted ID to be the same as the one in the imported record.
				$insertID = self::insert($table, $record);
				self::update($table, array("id"=>$id), "WHERE id=$insertID");
			}
			fclose($file);
		}

		////////////////////////////////////////////////////////////////////////////
		// Do an SQL INSERT.
		// Arguments:
		// $table is the name of the table.
		// $row is an associative array of field names and values. Example:
		// 	array(
		//			"name"=>"Barack+Obama"
		//			"password"=>"%241%24mdN55T9"
		//			"enable"=">1"
		//			"timestamp"=>"1210075624"
		//			);
		// The names and/or values can of course be PHP variables.
		public static function insert($table, $row)
		{
			$sql = "INSERT INTO $table (";
			$list = NULL;
			foreach ($row as $name=>$value)
			{
				if ($list) $list .= ", ";
				$list .= $name;
			}
			$sql .= "$list) VALUES (";
			$list = NULL;
			foreach ($row as $name=>$value)
			{
				if ($list) $list .= ", ";
				$list .= "'$value'";
			}
			$sql .= "$list)";
			//echo "$sql<br />";
			mysql_query($sql) or die('Error: '.mysql_error());
			return mysql_insert_id();
		}

		////////////////////////////////////////////////////////////////////////////
		// Do an SQL SELECT and return a single row of data.
		// Arguments:
		// $table is the name of the table.
		// $columns is an array of column names, or "*" for all columns.
		// $more is any further SQL such as WHERE and ORDER BY clauses.
		public static function selectRow($table, $columns, $more = NULL)
		{
			$result = self::_select($table, FALSE, $columns, $more);
			if (mysql_num_rows($result))
			{
				$row = mysql_fetch_object($result);
				mysql_free_result($result);
				return $row;
			}
			return NULL;
		}

		////////////////////////////////////////////////////////////////////////////
		// Do an SQL SELECT and return the data.
		// Arguments:
		// $table is the name of the table.
		// $columns is an array of column names, or "*" for all columns.
		// $more is any further SQL such as WHERE and ORDER BY clauses.
		public static function select($table, $columns, $more = NULL)
		{
			return self::_select($table, FALSE, $columns, $more);
		}

		////////////////////////////////////////////////////////////////////////////
		// Do an SQL SELECT DISTINCT and return the data.
		// Arguments:
		// $table is the name of the table.
		// $columns is an array of column names, or "*".
		// $more is any further SQL such as WHERE and ORDER BY clauses.
		public static function selectDistinct($table, $columns, $more = NULL)
		{
			return self::_select($table, TRUE, $columns, $more);
		}

		////////////////////////////////////////////////////////////////////////////
		// Do a generic SQL SELECT and return the data.
		// Arguments:
		// $table is the name of the table.
		// If $distinct is TRUE add the DISTINCT modifier.
		// $columns is an array of column names, or "*".
		// $more is any further SQL such as WHERE and ORDER BY clauses.
		private static function _select($table, $distinct, $columns, $more)
		{
			$sql = "SELECT ";
			if ($distinct) $sql .= "DISTINCT ";
			if ($columns == "*") $sql .= "*";
			else
			{
				$list = NULL;
				foreach ($columns as $column)
				{
					if ($list) $list .= ",";
					$list .= $column;
				}
				$sql .= $list;
			}
			$sql .= " FROM $table";
			if ($more) $sql .= " $more";
			//echo "$sql<br />";
			$result = mysql_query($sql) or die("Could not run query: ".mysql_error());
			return $result;
		}

		////////////////////////////////////////////////////////////////////////////
		// Count the number of rows returned by a query.
		// Arguments:
		// $table is the name of the table.
		// $more is any further SQL such as WHERE and ORDER BY clauses.
		public static function countRows($table, $more = NULL)
		{
			$result = self::_select($table, FALSE, array('id'), $more);
			$count = mysql_num_rows($result);
			DB::freeResult($result);
			return $count;
		}

		////////////////////////////////////////////////////////////////////////////
		// Count the number of rows returned by a query.
		// This is used where you already have a result object.
		// Arguments:
		// $result is the result of the query.
		public static function nRows($result)
		{
			return mysql_num_rows($result);
		}

		////////////////////////////////////////////////////////////////////////////
		// Fetch a result row from a query result.
		// Arguments:
		// $result is the result of the query.
		public static function fetchRow($result)
		{
			return mysql_fetch_object($result);
		}

		////////////////////////////////////////////////////////////////////////////
		// Free the result row.
		// Arguments:
		// $result is the query result.
		public static function freeResult($result)
		{
			mysql_free_result($result);
		}

		////////////////////////////////////////////////////////////////////////////
		// Do an SQL UPDATE.
		// Arguments:
		// $table is the name of the table.
		// $row is an associative array of field names and values,
		//		similar to the example given for insert().
		// $where is the WHERE clause.
		public static function update($table, $row, $where = NULL)
		{
			$sql = "UPDATE $table SET ";
			$list = NULL;
			foreach ($row as $name=>$value)
			{
				if ($list) $list .= ", ";
				$list .= "$name='$value'";
			}
			$sql .= "$list $where";
			//echo "$sql<br />";
			mysql_query($sql) or die('Error: '.mysql_error());
		}

		////////////////////////////////////////////////////////////////////////////
		// Do an SQL DELETE.
		// Arguments:
		// $table is the name of the table.
		// $where is the WHERE clause.
		public static function delete($table, $where)
		{
			$sql = "DELETE FROM $table $where";
			//echo "$sql<br />";
			mysql_query($sql) or die('Error: '.mysql_error());
		}
	}
?>