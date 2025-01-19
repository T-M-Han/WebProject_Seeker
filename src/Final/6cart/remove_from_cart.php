<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true && isset($_SESSION['customerid'])) {
        $customerId = $_SESSION['customerid'];
        if (isset($_POST['cartid'])) {
            $mysqli = new mysqli("localhost", "root", "", "seekerdb");
            if ($mysqli->connect_errno) {
                echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                exit();
            }

            $cartid = $_POST['cartid'];
            $fetch_cart_query = "SELECT sneakerid, sizeid FROM cart WHERE cartid = ?";
            $fetch_cart_stmt = $mysqli->prepare($fetch_cart_query);
            $fetch_cart_stmt->bind_param("i", $cartid);
            $fetch_cart_stmt->execute();
            $fetch_cart_stmt->store_result();
            $fetch_cart_stmt->bind_result($sneakerId, $sizeId);
            $fetch_cart_stmt->fetch();
            $fetch_cart_stmt->close();

            $stmt = $mysqli->prepare("DELETE FROM cart WHERE customerid = ? AND cartid = ?");
            $stmt->bind_param("ii", $customerId, $_POST['cartid']);

            if ($stmt->execute()) {
                $updateInventoryQuery = "UPDATE inventory SET quantity = quantity + 1 
                                         WHERE sneakerid = ? AND sizeid = ?";
                $updateInventoryStmt = $mysqli->prepare($updateInventoryQuery);
                $updateInventoryStmt->bind_param("ii", $sneakerId, $sizeId);
                $updateInventoryStmt->execute();

                echo "Item removed successfully and inventory updated";
            } else {
                echo "Error removing item: " . $stmt->error;
            }

            $stmt->close();
            $mysqli->close();
        } else {
            echo "Cart ID not provided";
        }
    } else {
        echo "User not logged in or customer ID not set";
    }
} else {
    echo "Invalid request method";
}
?>
