<?php
session_start();

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header('Location: ../2login/1mainlogin.php');
    exit();
}

$customerId = $_SESSION['customerid'];

$sneakerId = $_POST['sneakerid'];
$sizeId = $_POST['sizeid'];
$date = date("Y-m-d H:i:s");

$mysqli = new mysqli("localhost", "root", "", "seekerdb");

if ($mysqli->connect_errno) {
    echo json_encode(array('success' => false, 'message' => 'Failed to connect to MySQL: ' . $mysqli->connect_error));
    exit();
}

$inventory_stmt = $mysqli->prepare("SELECT quantity FROM inventory WHERE sneakerid = ? AND sizeid = ?");
$inventory_stmt->bind_param("ii", $sneakerId, $sizeId);
$inventory_stmt->execute();
$inventory_stmt->store_result();

if ($inventory_stmt->num_rows == 1) {
    $inventory_stmt->bind_result($quantity);
    $inventory_stmt->fetch();

    if ($quantity > 0) {
        $cart_stmt = $mysqli->prepare("INSERT INTO cart (customerid, sneakerid, sizeid, date) VALUES (?, ?, ?, ?)");
        $cart_stmt->bind_param("iiis", $customerId, $sneakerId, $sizeId, $date);

        if ($cart_stmt->execute()) {
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Error adding item to cart: ' . $mysqli->error));
        }

        $cart_stmt->close();
    } else {
        echo json_encode(array('success' => false, 'message' => 'Desired quantity not available in inventory'));
    }
} else {
    echo '<script>window.location.href = window.location.href;</script>';
    exit();
}

$inventory_stmt->close();
$mysqli->close();
?>
