<?php
// get_table_sql.php
header('Content-Type: text/plain');

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'newsnest_hub';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['table'])) {
    $table = $conn->real_escape_string($_GET['table']);
    
    // Get CREATE TABLE statement
    $result = $conn->query("SHOW CREATE TABLE $table");
    if ($result && $row = $result->fetch_assoc()) {
        echo $row['Create Table'];
    } else {
        echo "Unable to fetch SQL for table: $table";
    }
}

$conn->close();
?>