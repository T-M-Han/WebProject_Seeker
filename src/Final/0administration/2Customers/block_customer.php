<?php
$mysqli = new mysqli("localhost", "root", "", "seekerdb");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = $_POST['customer_id'];
    $action = $_POST['action'];

    if ($action == 'block') {
        $updateQuery = "UPDATE `customer` SET `block` = 1 WHERE `customerid` = $customer_id";
    } else {
        $updateQuery = "UPDATE `customer` SET `block` = 0 WHERE `customerid` = $customer_id";
    }

    if ($mysqli->query($updateQuery) === TRUE) {
        header("Location: 2cscenter.php");
        exit();
    } else {
        echo "Error updating record: " . $mysqli->error;
    }
}

$mysqli->close();
?>
