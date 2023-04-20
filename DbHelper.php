<?php
$mysqli = new mysqli('localhost', 'devoup_db_root', 'x$nte4araw*&x~g4@f', 'shmc');
$mysqli->set_charset('utf8mb4');

if ($mysqli->connect_errno) {
    exit("Failed to connect to MySQL: " . $mysqli->connect_errno);
}

function db_query(string $query, string $data_types = '', ...$values)
{
    global $mysqli;
    $stmt = $mysqli->prepare($query);
    if ($mysqli->errno != 0) {
        error_log("SQL ERROR:" . $mysqli->error);
        return [];
    }
    if (!empty($data_types))
        $stmt->bind_param($data_types, ...$values);
    $stmt->execute();
    return $stmt->get_result();
}

/*
$data = db_query(
    "SELECT * FROM table WHERE id = ? AND name = ?",
    "is",
    $id,
    $name
)->fetch_all(MYSQLI_ASSOC);

db_query(
    "INSERT INTO table (col1, col2, col3) VALUES (?, ?, ?)",
    "isi",
    $id,
    $name,
    $amount
);

->num_rows                                  <-- return total rows count
->fetch_all(MYSQLI_ASSOC)                   <-- fetch all rows and return the result-set as an associative array
                                             -- MYSQLI_ASSOC | MYSQLI_NUM (default) | MYSQLI_BOTH
->fetch_array()                             <-- fetch a result row as a numeric array and as an associative array
                                             -- MYSQLI_ASSOC | MYSQLI_NUM (default) | MYSQLI_BOTH (default)
                                             -- $row[0] (or) $row['foo']
->fetch_assoc()                             <-- fetch a result row as an associative array
                                             -- $row['foo']
*/
