<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "seekerdb");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

$paymentMethod = $_POST['payment_method'];
$customerId = $_SESSION['customerid'];

$allowOrder = true;
foreach ($_SESSION['cart'] as $key => $item) {
    $sneakerId = $item['sneakerid'];
    $sizeId = $item['sizeid'];

    // Retrieve sneaker name
    $sneakerQuery = "SELECT name FROM sneakers WHERE sneakerid = '$sneakerId'";
    $sneakerResult = $mysqli->query($sneakerQuery);
    $sneakerName = "";
    if ($sneakerResult) {
        $sneakerData = $sneakerResult->fetch_assoc();
        $sneakerName = $sneakerData['name'];
    }

    // Retrieve size name
    $sizeQuery = "SELECT sizename FROM sizes WHERE sizeid = '$sizeId'";
    $sizeResult = $mysqli->query($sizeQuery);
    $sizeName = "";
    if ($sizeResult) {
        $sizeData = $sizeResult->fetch_assoc();
        $sizeName = $sizeData['sizename'];
    }

    $checkInventoryQuery = "SELECT quantity FROM inventory WHERE sneakerid = '$sneakerId' AND sizeid = '$sizeId'";
    $inventoryResult = $mysqli->query($checkInventoryQuery);

    if ($inventoryResult) {
        $inventoryData = $inventoryResult->fetch_assoc();
        $quantityInInventory = $inventoryData['quantity'];

        if ($quantityInInventory <= 0) {
            $deleteFromCartQuery = "DELETE FROM cart WHERE customerid = '$customerId' AND sneakerid = '$sneakerId' AND sizeid = '$sizeId'";
            $mysqli->query($deleteFromCartQuery);

            $errorMsg = "Sorry, the selected sneaker ($sneakerName) in size ($sizeName) is out of stock.";
            echo $errorMsg;
            exit();
        }
    } else {
        $errorMsg = "Error checking inventory: " . $mysqli->error;
        echo $errorMsg;
        exit();
    }
}

echo 'success';
?>
