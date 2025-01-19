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
$sneakerbrand = "NIKE";
$sneakersQuery = "SELECT * FROM sneakers WHERE brand='$sneakerbrand'";
$sneakersResult = $mysqli->query($sneakersQuery);
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
    <link rel="stylesheet" href="main.css">
    <script src="main.js" defer></script>
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
    <a href="1new.php">NEW RELEASES</a>
    <a href="2jordan.php">JORDAN</a>
    <a href="3nike.php">NIKE</a>
    <a href="4yeezy.php">YEEZY</a>
    <a href="5newbalance.php">NEW BALANCE</a>
    <a href="6adidas.php">ADIDAS</a>
    <a href="7womens.php">WOMENS</a>
    <a href="8kids.php">KIDS</a>
</nav>
<div class="sneakers-section">
    <div class="sneakers-container">
        <?php while ($row = $sneakersResult->fetch_assoc()): ?>
            <div class="sneaker">
                <a href="../4buy/buysection.php?sneakerid=<?= $row['sneakerid'] ?>" style="text-decoration: none; color: black;">
                    <img src="data:image/jpeg;base64,<?= base64_encode($row['image']) ?>" alt="<?= $row['name'] ?>">
                    <h3><?= $row['brand'] ?></h3>
                    <p><?= $row['name'] ?></p>
                    <p>"<?= $row['nickname'] ?>"</p>
                    <?php
                    $priceQuery = "SELECT MIN(price) AS lowest_price FROM prices WHERE sneakerid = " . $row['sneakerid'];
                    $priceResult = $mysqli->query($priceQuery);
                    $priceRow = $priceResult->fetch_assoc();
                    $lowestPrice = isset($priceRow['lowest_price']) ? $priceRow['lowest_price'] : 'N/A';
                    ?>
                    <p>Lowest Ask: $<?= $lowestPrice ?></p>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
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
