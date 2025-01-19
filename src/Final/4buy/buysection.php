<?php
session_start();

$loggedIn = isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true;

$mysqli = new mysqli("localhost", "root", "", "seekerdb");

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

function isUserBlocked($customerId) {
    global $mysqli;

    $customerId = $mysqli->real_escape_string($customerId);

    $blockedQuery = "SELECT block FROM customer WHERE customerid = $customerId";
    $blockedResult = $mysqli->query($blockedQuery);
    if ($blockedResult && $blockedResult->num_rows > 0) {
        $row = $blockedResult->fetch_assoc();
        return intval($row['block']) === 1;
    }

    return false;
}

if ($loggedIn && isset($_SESSION['customerid'])) {
    $customerId = $_SESSION['customerid'];
    if (isUserBlocked($customerId)) {
        $error_message = urlencode("Your account has been blocked due to the policy of our company. Please contact support for assistance or create a new account.");
        header("Location: ../2login/1mainlogin.php?error_message=$error_message");
        exit();
    }
}

function fetchCartItemCount($loggedIn) {
    if ($loggedIn && isset($_SESSION['customerid'])) {
        $customerId = $_SESSION['customerid'];

        $mysqli = new mysqli("localhost", "root", "", "seekerdb");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: " . $mysqli->connect_error;
            exit();
        }

        $countQuery = "SELECT COUNT(*) FROM cart WHERE customerid = $customerId";
        $countResult = $mysqli->query($countQuery);
        $rowCount = $countResult->fetch_row();
        $cartItemCount = $rowCount[0];

        return $cartItemCount;
    } else {
        return 0;
    }
}

$cartItemCount = fetchCartItemCount($loggedIn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="icon" href="../images/SK.logo.png">
    <title>SEEKER-Sneaker Store</title>
    <link rel="stylesheet" href="buy.css">
    <script src="addtocart.js" defer></script>
</head>
<body>
    <header>
        <div class="header-text">
            <p>
                <a href="../3main/1new.php" style="color: inherit; text-decoration: none;">
                    SEEKER
                </a>
            </p>
        </div>
        <div class="header-icons">
            <a href="#" title="Search" id="searchIcon">
                <img src="../icons/search.png" alt="Search">
            </a>
            <div class="search-box" id="searchBox">
                <form id="searchForm">
                    <input type="text" name="query" placeholder="Search...">
                    <button type="submit">Search</button>
                </form>
            </div>
            <span>| </span>
            <?php if ($loggedIn): ?>
                <a href="../5profile/1profile.php" title="Profile" style="padding-left:5px;"><img src="../icons/profile.png" alt="Profile"></a>
            <?php else: ?>
                <a href="../2login/1mainlogin.php" title="User" style="padding-left:5px;"><img src="../icons/profile.png" alt="User"></a>
            <?php endif; ?>
            <?php if ($cartItemCount > 0): ?>
                <a href="../6cart/1cart.php" title="Shopping Cart" style="text-decoration: none;">
                    <img src="../icons/cart.png" alt="Shopping Cart">
                    <span id="cartItemCount"><?php echo $cartItemCount; ?></span>
                </a>
            <?php else: ?>
                <a href="../6cart/1cart.php" title="Shopping Cart">
                    <img src="../icons/cart.png" alt="Shopping Cart">
                </a>
            <?php endif; ?>
        </div>
        <button class="menu-toggle" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </header>
    
    <nav>
        <a href="../3main/1new.php">NEW RELEASES</a>
        <a href="../3main/2jordan.php">JORDAN</a>
        <a href="../3main/3nike.php">NIKE</a>
        <a href="../3main/4yeezy.php">YEEZY</a>
        <a href="../3main/5newbalance.php">NEW BALANCE</a>
        <a href="../3main/6adidas.php">ADIDAS</a>
        <a href="../3main/7womens.php">WOMENS</a>
        <a href="../3main/8kids.php">KIDS</a>
    </nav>

    <div class="container">
    <?php
    $mysqli = new mysqli("localhost", "root", "", "seekerdb");

    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: " . $mysqli->connect_error;
        exit();
    }

    $productID = $_GET['sneakerid'];
    $result = $mysqli->query("SELECT * FROM sneakers WHERE sneakerid = $productID");

    if ($row = $result->fetch_assoc()) {
        echo "<input type='hidden' id='productId' value='$productID'>";
        echo "<div class='sneaker-details'>";
        echo "<img src='data:image/jpeg;base64," . base64_encode($row['image']) . "' alt='Sneaker'>";
        echo "</div>";
        echo "<div class='sneaker-info'>";
        echo "<p style='font-size: medium; margin: 0px; color: grey;'>" . $row['brand'] . "</p>";
        echo "<h2>" . $row['name'] . "</h2>";
        echo "<h4> '" . $row['nickname'] . "'</h4>";

        $sizesQuery = "SELECT sizes.sizeid, sizes.sizename, prices.price, inventory.quantity 
                FROM sizes 
                LEFT JOIN prices ON sizes.sizeid = prices.sizeid AND prices.sneakerid = $productID
                LEFT JOIN inventory ON sizes.sizeid = inventory.sizeid AND inventory.sneakerid = $productID
                WHERE prices.price IS NOT NULL AND inventory.quantity > 0";

        $sizesResult = $mysqli->query($sizesQuery);

        echo "<form id='addToCartForm' action='addtocart.php' method='post'>";
        echo "<select id='sizeSelect' name='sizeid'>";

        if ($sizesResult->num_rows > 0) {
            while ($sizeRow = $sizesResult->fetch_assoc()) {
                $sizeId = $sizeRow['sizeid'];
                $sizeName = $sizeRow['sizename'];
                $price = isset($sizeRow['price']) ? "$" . $sizeRow['price'] : "Price not available";
                $quantity = $sizeRow['quantity'];
                $disabled = $quantity > 0 ? "" : "disabled";

                echo "<option value='$sizeId' data-quantity='$quantity' $disabled>EU $sizeName - $price </option>";
            }
        } else {
            echo "<option value='out_of_stock' selected>OUT OF STOCK</option>";
        }
        
        echo "</select><br><br>";

        $sizesResult->close();

        echo "<input type='hidden' name='sneakerid' value='$productID'>";
        echo "<button type='submit' class='buy-button'>ADD TO CART</button>";
        echo "</form>";
        echo "</div>";
    } else {
        echo "Product not found.";
    }

    $mysqli->close();
    ?>
</div>

<script>
    document.getElementById('sizeSelect').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var availableQuantity = parseInt(selectedOption.getAttribute('data-quantity'));
        
        if (availableQuantity === 0) {
            alert('This size is currently out of stock.');
            this.selectedIndex = 0;
        }
    });
</script>


    <h4 style="padding-left:30px; margin-bottom:10px;color: grey;">PRODUCT DEATAILS</h4>
    <div class="container3">
        <div class="table" style="width:7000px;">
            <div class="row">
                <div class="cell" rowspan="2">
                    <div class="sneakerdetails">
                        <?php echo $row['description']; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="table">
            <div class="row" style="height:75px;">
                <div class="cell">
                    <div class="brand">
                        <p style="font-size: small; margin: 0px; color: grey;">Brand</p>
                        <?php echo $row['brand']; ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="cell">
                    <div class="color">
                        <p style="font-size: small; margin: 0px; color: grey;">Colorway</p>
                        <?php echo $row['colorway']; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="table">
            <div class="row"style="height:75px;">
                <div class="cell">
                    <div class="genderdate">
                        <p style="font-size: small; margin: 0px; color: grey;">Gender</p>
                        <?php echo $row['gender']; ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="cell">
                    <div class="genderdate">
                        <p style="font-size: small; margin: 0px; color: grey;">ReleaseDate</p>
                        <?php echo $row['releasedate']; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="you-may-like">
        <h3>YOU May LIKE</h3>
        <?php
        $mysqli = new mysqli("localhost", "root", "", "seekerdb");

        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: " . $mysqli->connect_error;
            exit();
        }

        $query = "(
            SELECT s.*, IFNULL(lp.lowest_price, 'N/A') AS lowest_price 
            FROM sneakers s 
            LEFT JOIN (
                SELECT sneakerid, MIN(price) AS lowest_price 
                FROM prices 
                GROUP BY sneakerid
            ) AS lp ON s.sneakerid = lp.sneakerid
            WHERE s.sneakerid != $productID 
            AND s.sneakerid > $productID 
            ORDER BY RAND() 
            LIMIT 5
        ) UNION ALL (
            SELECT s.*, IFNULL(lp.lowest_price, 'N/A') AS lowest_price 
            FROM sneakers s 
            LEFT JOIN (
                SELECT sneakerid, MIN(price) AS lowest_price 
                FROM prices 
                GROUP BY sneakerid
            ) AS lp ON s.sneakerid = lp.sneakerid
            WHERE s.sneakerid != $productID 
            AND s.sneakerid < $productID 
            ORDER BY RAND() 
            LIMIT 5
        ) LIMIT 5";
        
        

        $result = $mysqli->query($query);

        while ($row = $result->fetch_assoc()) {
            echo "<div class='sneaker'>";
            $base64Image = base64_encode($row['image']);
            echo "<a href='buysection.php?sneakerid=" . $row['sneakerid'] . "' style='text-decoration: none; color: inherit;'>";
            echo "<img src='data:image/jpeg;base64," . $base64Image . "' alt='" . $row['name'] . "'>";
            echo "<br><label style='font-weight: bold; font-size: 14px;'>" . $row['brand']."</label>";
            echo "<br><label style='font-size: 12px;'>" . $row['name']."</label>";
            echo "<br><label style='font-size: 12px;'>".'"' . $row['nickname'].'"'."</label>";
            echo "<br><label style='color: #4a4949;font-size: 12px;'>Lowest Ask: $" . $row['lowest_price'] . "</label>";
            echo "</a>";
            echo "</div>";
        }

        $mysqli->close();
        ?>
    </div>
    
        <footer class="footer-section">
        <div class="footer-content">
            <div class="footer-info">
                <h3>ABOUT US</h3>
                <p>Welcome to SEEKER, your premier destination for the latest and most exclusive sneaker releases. Our passion for sneakers goes beyond footwear; it's a lifestyle we embrace and share with our community of fellow enthusiasts.</p>
                <p>Discover our carefully curated selection of sneakers that embody style, innovation, and quality. From iconic classics to limited editions, each pair in our collection is handpicked to ensure you're always stepping out in greatness.</p>
            </div>
            <div class="footer-info">
                <h3>CONTACT</h3>
                <p>Email: info@seeker.com<br>
                Phone: 123-456-7890<br>
                Visit us at: 123 Sneaker Street, City, Country</p>
                <p>Our customer support team is available during business hours to provide personalized assistance and answer any questions you may have about our products, orders, or services. Feel free to connect with us via email or phone for prompt and friendly support.</p>
            </div>
            <div class="footer-info">
                <h3>FOLLOW US</h3>
                <p>Stay connected with SEEKER for the latest releases, promotions, and behind-the-scenes updates:</p>
                <div class="social-icons">
                    <a href="https://www.facebook.com/YourSeekerPage" title="Facebook" target="_blank"><img src="../icons/facebook.png" alt="Facebook"></a>
                    <a href="https://www.twitter.com/YourSeekerTwitter" title="Twitter" target="_blank"><img src="../icons/twitter.png" alt="Twitter"></a>
                    <a href="https://www.instagram.com/YourSeekerInstagram" title="Instagram" target="_blank"><img src="../icons/instagram.png" alt="Instagram"></a>
                </div>
                <p>Have a question or want to share your sneaker passion with us? Feel free to tag us in your posts or send us a message. We love hearing from our followers!</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="footer-disclaimer">
                <p>DISCLAIMER: SEEKER is more than a sneaker store; it's a lifestyle. Explore our collection and step into greatness. <a href="1new.php" style="color:inherit;">Shop Here!</a></p>
            </div>
            <div class="footer-logo">
                <img src="../images/SEEKER.logo.png" alt="Seeker Logo">
            </div>
            <p>&copy; 2024 SEEKER. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>
