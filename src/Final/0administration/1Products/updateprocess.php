<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['sneakerid'])) {
        $sneakerupdateid = htmlspecialchars($_POST['sneakerid']);
        $mysqli = new mysqli("localhost", "root", "", "seekerdb");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: " . $mysqli->connect_error;
            exit();
        }

        $stmt = $mysqli->prepare("SELECT * FROM sneakers WHERE sneakerid = ?");
        $stmt->bind_param("i", $sneakerupdateid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $sneaker = $result->fetch_assoc();
            if (isset($_POST['name'], $_POST['brand'], $_POST['nickname'], $_POST['description'], $_POST['colorway'], $_POST['gender'], $_POST['releasedate'])) {
                $name = htmlspecialchars($_POST['name']);
                $brand = htmlspecialchars($_POST['brand']);
                $nickname = htmlspecialchars($_POST['nickname']);
                $description = htmlspecialchars($_POST['description']);
                $colorway = htmlspecialchars($_POST['colorway']);
                $gender = htmlspecialchars($_POST['gender']);
                $releasedate = htmlspecialchars($_POST['releasedate']);
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $imageData = file_get_contents($_FILES['image']['tmp_name']);
                }

                $update_stmt = $mysqli->prepare("UPDATE sneakers SET name=?, brand=?, nickname=?, description=?, colorway=?, gender=?, releasedate=?". (isset($imageData) ? ", image=?" : "") ." WHERE sneakerid=?");
                if (isset($imageData)) {
                    $update_stmt->bind_param("ssssssssi", $name, $brand, $nickname, $description, $colorway, $gender, $releasedate, $imageData, $sneakerupdateid);
                } else {
                    $update_stmt->bind_param("sssssssi", $name, $brand, $nickname, $description, $colorway, $gender, $releasedate, $sneakerupdateid);
                }
                $update_stmt->execute();
                $update_stmt->close();

                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'size_') === 0 && strpos($key, '_quantity') !== false) {
                        $sizeid = substr($key, 5, strpos($key, '_quantity') - 5);
                        $quantity = htmlspecialchars($value);
                        $inventory_stmt = $mysqli->prepare("UPDATE inventory SET quantity=? WHERE sneakerid=? AND sizeid=?");
                        $inventory_stmt->bind_param("iii", $quantity, $sneakerupdateid, $sizeid);
                        $inventory_stmt->execute();
                        $inventory_stmt->close();
                    }

                    if (strpos($key, 'size_') === 0 && strpos($key, '_price') !== false) {
                        $sizeid = substr($key, 5, strpos($key, '_price') - 5);
                        $price = htmlspecialchars($value);
                        $price_stmt = $mysqli->prepare("UPDATE prices SET price=? WHERE sneakerid=? AND sizeid=?");
                        $price_stmt->bind_param("dii", $price, $sneakerupdateid, $sizeid);
                        $price_stmt->execute();
                        $price_stmt->close();
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

                if (in_array($brandRedirect, ['jordan', 'nike', 'yeezy', 'newbalance', 'adidas'])) {
                    header("Location: {$brandRedirect}.php");
                    exit();
                } else {
                    exit();
                }
            }
        } else {
            echo "Sneaker not found";
        }

        $mysqli->close();
    } else {
        echo "Error: Sneaker ID not provided";
    }
} else {
    echo "Invalid request method";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="icon" href="../../images/SK.logo.png">
    <title>EDIT SNEAKER INFORMATION</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            color: #000000;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            display: inline-block;
            padding: 12px 24px;
            margin-right: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background-color: #000000;
            color: #fff;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #282828;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        img {
            max-width: 50%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        @media only screen and (max-width: 600px) {
            .container {
                padding: 15px;
            }
        }

        #releasedate {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        #image {
            width: 100%;
            padding: 6px;
            margin: 0px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            background-color: #f9f9f9;
            color: #333;
        }

        #image:hover {
            border-color: #000000;
        }

        #image:focus {
            outline: none;
            border-color: #000000;
        }

        #image::file-selector-button {
            padding: 10px;
            background-color: #9e9e9e;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        #image::file-selector-button:hover {
            background-color: #000000;
        }
        </style>
</head>
<body>
<div class="container">
    <h2>EDIT SNEAKER INFORMATION</h2>
    <form id="updateForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
        <?php
        $sneakerupdateid = isset($_POST['sneakerid']) ? htmlspecialchars($_POST['sneakerid']) : '';
        $mysqli = new mysqli("localhost", "root", "", "seekerdb");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: " . $mysqli->connect_error;
            exit();
        }

        $stmt = $mysqli->prepare("SELECT * FROM sneakers WHERE sneakerid = ?");
        $stmt->bind_param("i", $sneakerupdateid);
        $stmt->execute();
        $result = $stmt->get_result();

        $sneaker = ($result->num_rows > 0) ? $result->fetch_assoc() : null;

        $stmt->close();
        $mysqli->close();
        ?>
        <input type="hidden" name="sneakerid" value="<?php echo htmlspecialchars($sneakerupdateid); ?>">
        <label for="existingImage">Existing Image:</label>
        <?php if (!empty($sneaker) && !empty($sneaker['image'])) : ?>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($sneaker['image']); ?>" alt="Existing Image">
        <?php else : ?>
            <p>No existing image found.</p>
        <?php endif; ?>
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($sneaker['name'] ?? ''); ?>" required>
        <label for="brand">Brand:</label>
        <select id="brand" name="brand" onchange="checkBrand()" required>
            <option value="" disabled>Select Brand</option>
            <?php
            $mysqli = new mysqli("localhost", "root", "", "seekerdb");
            if ($mysqli->connect_errno) {
                echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                exit();
            }

            $result = $mysqli->query("SELECT DISTINCT brand FROM sneakers");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $selected = ($row['brand'] == $sneaker['brand']) ? 'selected' : '';
                    echo '<option value="' . $row['brand'] . '" ' . $selected . '>' . $row['brand'] . '</option>';
                }
            }
            $mysqli->close();
            ?>
        </select>
        <label for="nickname">Nickname:</label>
        <input type="text" id="nickname" name="nickname" value="<?php echo htmlspecialchars($sneaker['nickname'] ?? ''); ?>" required>
        <label for="description">Description:</label>
        <textarea id="description" name="description" style="height: 200px;resize:none;" required><?php echo htmlspecialchars($sneaker['description'] ?? ''); ?></textarea>
        <label for="colorway">Colorway:</label>
        <input type="text" id="colorway" name="colorway" value="<?php echo htmlspecialchars($sneaker['colorway'] ?? ''); ?>" required>
        <label for="gender">Gender:</label>
        <select id="gender" name="gender" required>
            <option value="mens" <?php echo ($sneaker['gender'] === 'mens') ? 'selected' : ''; ?>>Mens</option>
            <option value="womens" <?php echo ($sneaker['gender'] === 'womens') ? 'selected' : ''; ?>>Womens</option>
            <option value="kids" <?php echo ($sneaker['gender'] === 'kids') ? 'selected' : ''; ?>>Kids</option>
        </select>
        <br>
        <label for="releasedate">Release Date:</label>
        <input type="date" id="releasedate" name="releasedate" value="<?php echo htmlspecialchars($sneaker['releasedate'] ?? ''); ?>" max="<?php echo date('Y-m-d'); ?>" required>
        <label for="image">New Image:</label>
        <input type="file" id="image" name="image" accept="image/*"><br><br>
        <?php
        $mysqli = new mysqli("localhost", "root", "", "seekerdb");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: " . $mysqli->connect_error;
            exit();
        }

        $stmt = $mysqli->prepare("SELECT s.sizeid, s.sizename, p.price, i.quantity
                                   FROM sizes s
                                   LEFT JOIN prices p ON s.sizeid = p.sizeid AND p.sneakerid = ?
                                   LEFT JOIN inventory i ON s.sizeid = i.sizeid AND i.sneakerid = ?
                                   ORDER BY s.sizeid");
        if (!$stmt) {
            echo "Failed to prepare statement: " . $mysqli->error;
            exit();
        }

        $stmt->bind_param("ii", $sneakerupdateid, $sneakerupdateid);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<table>';
        echo '<tr><th>Size</th><th>Quantity</th><th>Price</th></tr>';

        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>EU ' . htmlspecialchars($row['sizename']) . '</td>';
            echo '<td><input type="number" name="size_' . $row['sizeid'] . '_quantity" min="0" value="' . (htmlspecialchars($row['quantity']) ?? 0) . '" required></td>';
            echo '<td><input type="number" name="size_' . $row['sizeid'] . '_price" min="0" step="0.01" value="' . (htmlspecialchars($row['price']) ?? 0) . '" required></td>';
            echo '</tr>';
        }

        echo '</table>';
        $stmt->close();
        $mysqli->close();
        ?>
        <div style="overflow: hidden; display:flex; justify-content: space-between;">
            <button type="button" onclick="history.back()">Back</button>
            <button type="submit">Update</button>
        </div>
    </form>
</div>
</body>
</html>