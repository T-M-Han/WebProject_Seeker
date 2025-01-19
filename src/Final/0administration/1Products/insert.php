<?php
$mysqli = new mysqli("localhost", "root", "", "seekerdb");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $nickname = $_POST['nickname'];
    $description = $_POST['description'];
    $colorway = $_POST['colorway'];
    $gender = $_POST['gender'];
    $releasedate = $_POST['releasedate'];

    $image = $_FILES['image']['tmp_name'];
    $imageData = file_get_contents($image);

    $insertSneakerQuery = "INSERT INTO sneakers (name, brand, nickname, description, colorway, gender, releasedate, image) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $mysqli->prepare($insertSneakerQuery);
    $stmt->bind_param("ssssssss", $name, $brand, $nickname, $description, $colorway, $gender, $releasedate, $imageData);

    if ($stmt->execute()) {
        $sneakerId = $mysqli->insert_id;
        $stmt->close();

        foreach ($_POST as $key => $value) {
            if (strpos($key, 'size_') === 0) {
                $parts = explode('_', $key);
                $sizeId = $parts[1];
                
                if (isset($_POST["size_{$sizeId}_quantity"]) && isset($_POST["size_{$sizeId}_price"])) {
                    $quantity = $_POST["size_{$sizeId}_quantity"];
                    $price = $_POST["size_{$sizeId}_price"];

                    $checkQuery = "SELECT * FROM inventory WHERE sneakerid = ? AND sizeid = ?";
                    $stmtCheck = $mysqli->prepare($checkQuery);
                    $stmtCheck->bind_param("ii", $sneakerId, $sizeId);
                    $stmtCheck->execute();
                    $resultCheck = $stmtCheck->get_result();

                    if ($resultCheck->num_rows == 0) {
                        $insertInventoryQuery = "INSERT INTO inventory (sneakerid, sizeid, quantity) VALUES (?, ?, ?)";
                        $stmtInventory = $mysqli->prepare($insertInventoryQuery);
                        $stmtInventory->bind_param("iii", $sneakerId, $sizeId, $quantity);
                        $stmtInventory->execute();
                        $stmtInventory->close();

                        $insertPriceQuery = "INSERT INTO prices (sneakerid, sizeid, price) VALUES (?, ?, ?)";
                        $stmtPrice = $mysqli->prepare($insertPriceQuery);
                        $stmtPrice->bind_param("iid", $sneakerId, $sizeId, $price);
                        $stmtPrice->execute();
                        $stmtPrice->close();
                    }

                    $stmtCheck->close();
                }
            }
        }

        $brandRedirect = strtolower(str_replace(' ', '', $brand));
        switch ($brandRedirect) {
            case 'jordan':
                header("Location: 1jmcenter.php");
                exit();
            case 'nike':
                header("Location: 2nmcenter.php");
                exit();
            case 'yeezy':
                header("Location: 3ymcenter.php");
                exit();
            case 'newbalance':
                header("Location: 4nbmcenter.php");
                exit();
            case 'adidas':
                header("Location: 5amcenter.php");
                exit();
            default:
                exit();
        }
    } else {
        echo "Error: " . $insertSneakerQuery . "<br>" . $mysqli->error;
    }
}

$mysqli->close();
?>
