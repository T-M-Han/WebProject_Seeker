<?php
session_start();
if (!isset($_SESSION["loggedIn"])) {
header("Location: ../2login/1mainlogin.php");
exit();
}

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
    <link rel="stylesheet" href="profile.css">
    <script src="profile.js" defer></script>
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

    <div class="container3">
        <div class="table">
            <div class="row">
                <div class="cell" rowspan="2">
                    <div class="profile">
                        <?php include 'get_customer_data.php'; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table">
            <div class="row">
                <div class="cell">
                    <div class="orders" style="text-align: center;padding:0px;">
                        <h2>ORDERS</h2>
                        <?php
                        $mysqli = new mysqli("localhost", "root", "", "seekerdb");
                        if ($mysqli->connect_errno) {
                            echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                            exit();
                        }

                        $customerId = $_SESSION['customerid'];
                        $ordersQuery = "SELECT `orderid`, `orderdate`, `paymethod`, `transactionno`, `totalamount`, `status` FROM `orders` WHERE `customerid` = $customerId";
                        $ordersResult = $mysqli->query($ordersQuery);

                        if ($ordersResult->num_rows > 0) {
                            echo '<table class="responsive-table">';
                            echo '<tr><th>Order ID</th><th>Order Date</th><th>Payment Method</th><th>Transaction No</th><th>Total Amount</th><th>Status</th><th>Details</th></tr>';

                            while ($row = $ordersResult->fetch_assoc()) {
                                $orderId = $row['orderid'];
                                $hashedOrderId = strtoupper(substr(md5($orderId), 0, 5));
                                echo '<tr>';
                                echo '<td >' . $row['orderid'] . '</td>';
                                echo '<td >' . $row['orderdate'] . '</td>';
                                echo '<td >' . $row['paymethod'] . '</td>';
                                echo '<td >' . $row['transactionno'] . '</td>';
                                echo '<td >$' . $row['totalamount'] . '</td>';
                                echo '<td >' . $row['status'] . '</td>';
                                echo '<td ><span class="toggle-details" style="color:black;"data-orderid="' . $orderId . '">Details</span></td>';
                                echo '</tr>';

                                echo '<tr class="order-details-row" id="orderDetailsRow_' . $orderId . '" style="display: none;">';
                                echo '<td colspan="8" style="padding:0px;"><div class="order-details-container" id="orderDetailsContainer_' . $orderId . '"></div></td>';
                                echo '</tr>';
                            }
                            echo '</table>';
                        } else {
                            echo 'No orders found.';
                        }

                        $mysqli->close();
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="table">
            <div class="row">
                <div class="cell">
                    <div class="logout">
                        <form action="logout.php" method="post">
                            <label class="logout-label">Are you sure you want to logout?</label>
                            <button type="submit" name="logout" class="logout-button">LOGOUT</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.toggle-details');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-orderid');
                    const detailsContainer = document.getElementById('orderDetailsContainer_' + orderId);

                    const detailsRow = document.getElementById('orderDetailsRow_' + orderId);
                    if (detailsRow.style.display === 'none' || detailsRow.style.display === '') {
                        fetch('get_order_details.php?orderid=' + orderId)
                            .then(response => response.text())
                            .then(data => {
                                detailsContainer.innerHTML = data;
                                detailsRow.style.display = 'table-row';
                            })
                            .catch(error => {
                                console.error('Error fetching order details:', error);
                            });
                    } else {
                        detailsRow.style.display = 'none';
                    }
                });
            });
        });
    </script>

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
