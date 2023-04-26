<?php

$con = mysqli_connect('localhost', 'devoup_db_root', 'x$nte4araw*&x~g4@f', 'shmc');

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error() . PHP_EOL;
}

function db_query(string $query, string $data_types = '', ...$values)
{
    /// prepare, bind and execute a prepared statement

    /// $data = db_query(
    ///   "SELECT * FROM table WHERE id = ? AND name = ?",  <-- query string same as prepare https://www.php.net/manual/en/mysqli.quickstart.prepared-statements.php
    ///   "is",                                             <-- data types same as bind_param https://www.php.net/manual/en/mysqli-stmt.bind-param
    ///   $id, $name                                        <-- values same as bind_param
    /// )->fetch_all(MYSQLI_ASSOC);                         <-- returns a mysqli_result https://www.php.net/manual/en/class.mysqli-result.php

    /// db_query(
    ///   "INSERT INTO table (col1, col2, col3) VALUES (?, ?, ?)",
    ///   "isi",
    ///   $id, $name, $amount  
    /// );

    global $con;
    $stmt = $con->prepare($query);
    if (!empty($data_types))
        $stmt->bind_param($data_types, ...$values);
    $stmt->execute();

    /*$rc = $stmt->execute();
	if ( false===$rc ) {
	  exit('execute() failed: ' . htmlspecialchars($stmt->error));
	}*/
    return $stmt->get_result();
}
