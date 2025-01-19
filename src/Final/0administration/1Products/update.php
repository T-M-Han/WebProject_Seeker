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
    $sneakerId = $_POST['sneakerId'];

    $image = $_FILES['image']['tmp_name'];
    $imageData = file_get_contents($image);

    $updateSneakerQuery = "UPDATE sneakers 
                           SET name = ?, brand = ?, nickname = ?, description = ?, colorway = ?, gender = ?, releasedate = ?, image = ? 
                           WHERE id = ?";

    $stmt = $mysqli->prepare($updateSneakerQuery);
    $stmt->bind_param("ssssssssi", $name, $brand, $nickname, $description, $colorway, $gender, $releasedate, $imageData, $sneakerId);

    if ($stmt->execute()) {
        $stmt->close();

        foreach ($_POST as $key => $value) {
            if (strpos($key, 'size_') === 0) {
                $parts = explode('_', $key);
                $sizeId = $parts[1];

                if (isset($_POST["size_{$sizeId}_quantity"]) && isset($_POST["size_{$sizeId}_price"])) {
                    $quantity = $_POST["size_{$sizeId}_quantity"];
                    $price = $_POST["size_{$sizeId}_price"];

                    $updateInventoryQuery = "UPDATE inventory SET quantity = ? WHERE sneakerid = ? AND sizeid = ?";
                    $stmtInventory = $mysqli->prepare($updateInventoryQuery);
                    $stmtInventory->bind_param("iii", $quantity, $sneakerId, $sizeId);
                    $stmtInventory->execute();
                    $stmtInventory->close();

                    $updatePriceQuery = "UPDATE prices SET price = ? WHERE sneakerid = ? AND sizeid = ?";
                    $stmtPrice = $mysqli->prepare($updatePriceQuery);
                    $stmtPrice->bind_param("dii", $price, $sneakerId, $sizeId);
                    $stmtPrice->execute();
                    $stmtPrice->close();
                }
            }
        }

        echo "Sneaker updated successfully!";
    } else {
        echo "Error: " . $updateSneakerQuery . "<br>" . $mysqli->error;
    }
}

$mysqli->close();
?>
