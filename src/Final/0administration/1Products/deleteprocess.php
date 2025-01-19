<?php
$mysqli = new mysqli("localhost", "root", "", "seekerdb");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['sneakerid'])) {
        $sneakerId = $_POST['sneakerid'];

        $checkCartQuery = "SELECT * FROM cart WHERE sneakerid = ?";
        $stmtCheckCart = $mysqli->prepare($checkCartQuery);
        if (!$stmtCheckCart) {
            echo "Error: Failed to prepare cart query: " . $mysqli->error;
            exit();
        }
        $stmtCheckCart->bind_param("i", $sneakerId);
        $stmtCheckCart->execute();
        $resultCart = $stmtCheckCart->get_result();
        $stmtCheckCart->close();

        $checkOrderDetailsQuery = "SELECT * FROM orderdetail WHERE sneakerid = ?";
        $stmtCheckOrderDetails = $mysqli->prepare($checkOrderDetailsQuery);
        if (!$stmtCheckOrderDetails) {
            echo "Error: Failed to prepare orderdetails query: " . $mysqli->error;
            exit();
        }
        $stmtCheckOrderDetails->bind_param("i", $sneakerId);
        $stmtCheckOrderDetails->execute();
        $resultOrderDetails = $stmtCheckOrderDetails->get_result();
        $stmtCheckOrderDetails->close();

        if ($resultCart->num_rows > 0 || $resultOrderDetails->num_rows > 0) {
            echo '<script>window.history.back();</script>';
            exit();
        } else {
            $deletePricesQuery = "DELETE FROM prices WHERE sneakerid = ?";
            $stmtDeletePrices = $mysqli->prepare($deletePricesQuery);
            if (!$stmtDeletePrices) {
                echo "Error: Failed to prepare prices deletion query: " . $mysqli->error;
                exit();
            }
            $stmtDeletePrices->bind_param("i", $sneakerId);
            $stmtDeletePrices->execute();
            $stmtDeletePrices->close();

            $deleteInventoryQuery = "DELETE FROM inventory WHERE sneakerid = ?";
            $stmtDeleteInventory = $mysqli->prepare($deleteInventoryQuery);
            if (!$stmtDeleteInventory) {
                echo "Error: Failed to prepare inventory deletion query: " . $mysqli->error;
                exit();
            }
            $stmtDeleteInventory->bind_param("i", $sneakerId);
            $stmtDeleteInventory->execute();
            $stmtDeleteInventory->close();

            $deleteSneakerQuery = "DELETE FROM sneakers WHERE sneakerid = ?";
            $stmtDeleteSneaker = $mysqli->prepare($deleteSneakerQuery);
            if (!$stmtDeleteSneaker) {
                echo "Error: Failed to prepare sneaker deletion query: " . $mysqli->error;
                exit();
            }
            $stmtDeleteSneaker->bind_param("i", $sneakerId);
            $stmtDeleteSneaker->execute();
            $stmtDeleteSneaker->close();

            echo '<script>window.location.href = "1jmcenter.php";</script>';
            exit();
        }
    } else {
        echo "Error: Sneaker ID is missing.";
    }
} else {
    echo "Error: Invalid request method.";
}
$mysqli->close();
?>
