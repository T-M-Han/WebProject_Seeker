<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "seekerdb");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

$paymentMethod = $_POST['payment_method'];
$totalAmount = $_POST['total_amount'];
$serialNumber = $_POST['serial_number'];
$customerId = $_SESSION['customerid'];

$orderDate = date('Y-m-d H:i:s');
$status = 'Processing';

$insertOrderQuery = "INSERT INTO orders (customerid, orderdate, paymethod, transactionno, totalamount, status)
                     VALUES ('$customerId', '$orderDate', '$paymentMethod', '$serialNumber', '$totalAmount', '$status')";

if ($mysqli->query($insertOrderQuery)) {
    $orderId = $mysqli->insert_id;

    foreach ($_SESSION['cart'] as $item) {
        $sneakerId = $item['sneakerid'];
        $sizeId = $item['sizeid'];
        $price = $item['price'];

        $insertOrderDetailQuery = "INSERT INTO orderdetail (orderid, sneakerid, sizeid, unitprice)
                                   VALUES ('$orderId', '$sneakerId', '$sizeId', '$price')";
        
        if ($mysqli->query($insertOrderDetailQuery)) {
            $updateInventoryQuery = "UPDATE inventory SET quantity = quantity - 1 
                                     WHERE sneakerid = '$sneakerId' AND sizeid = '$sizeId'";
            $mysqli->query($updateInventoryQuery);
        } else {
            echo "Error inserting order details: " . $mysqli->error;
        }
    }

    $deleteCartQuery = "DELETE FROM cart WHERE customerid = '$customerId'";
    if ($mysqli->query($deleteCartQuery)) {
        header("Location: receipt.php?orderid=$orderId");
        exit();
    } else {
        echo "Error deleting cart items: " . $mysqli->error;
    }
} else {
    echo "Error placing order: " . $mysqli->error;
}

$mysqli->close();
?>
