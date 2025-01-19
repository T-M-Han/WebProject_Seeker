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

$sneakersResult = null;

if ($loggedIn && isset($_SESSION['customerid'])) {
    $customerId = $_SESSION['customerid'];

    $sneakersQuery = "SELECT cart.cartid,sneakers.*,sizes.*,prices.price
    FROM cart
    JOIN sneakers ON cart.sneakerid = sneakers.sneakerid
    JOIN sizes ON cart.sizeid = sizes.sizeid
    JOIN prices ON cart.sneakerid = prices.sneakerid AND cart.sizeid = prices.sizeid
    WHERE cart.customerid = $customerId";

    $sneakersResult = $mysqli->query($sneakersQuery);

    if (!$sneakersResult) {
        echo "Error executing query: " . $mysqli->error;
    }
}

$totalAmount = 0;

if ($sneakersResult && $sneakersResult->num_rows > 0) {
    while ($row = $sneakersResult->fetch_assoc()) {
        $totalAmount += $row['price'];
    }
    $sneakersResult->data_seek(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="icon" href="../images/SK.logo.png">
    <title>SEEKER-Sneaker Store</title>
    <link rel="stylesheet" href="cart.css">
    <script src="cart.js" defer></script>
</head>
<body>
<header>
    <div class="header-text">
        <p>
            <a href="../3main/1new.php" style="color: inherit; text-decoration: none;">SEEKER</a>
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
            <a href="1cart.php" title="Shopping Cart" style="text-decoration: none;">
                <img src="../icons/cart.png" alt="Shopping Cart">
                <span id="cartItemCount"><?php echo $cartItemCount; ?></span>
            </a>
        <?php else: ?>
            <a href="#" title="Shopping Cart">
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
<p style="font-size: 18px;font-weight: bold;text-align:center;margin: 20px;margin-top: 30px;">
    SHOPPING CART
</p>
<div class="cartcontainer" style="display: flex;justify-content: center;">
    <div class="carttable" style="width: 1000px; padding-left:0px;">
        <?php if ($loggedIn && isset($sneakersResult) && $sneakersResult->num_rows > 0): ?>
            <div class="cart-table">
                <table>
                    <tbody>
                        <?php while ($row = $sneakersResult->fetch_assoc()): ?>
                            <tr>
                                <td style="padding-bottom: 20px;">
                                    <img src='data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>' alt='Sneaker'>
                                </td>
                                <td>
                                    <label style="font-size: 16px;font-weight: bold;padding-right:20px;">
                                        <?php echo $row['name']; ?><br>
                                    </label>
                                    <label style="font-size: 14px;padding-right:20px;">
                                        "<?php echo $row['nickname']; ?>"<br> 
                                    </label>
                                    <label style="font-size: 12px;padding-right:20px;">
                                        Size: EU <?php echo $row['sizename']; ?><br>
                                    </label>
                                    <button class="remove-item" style="text-decoration:underline;font-weight:bold;"data-cartid="<?php echo $row['cartid']; ?>">Remove</button>
                                </td>
                                <td style="font-weight:bold;">
                                    <?php echo "$" . $row['price']; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align: center;">
                <?php if (!$loggedIn): ?>
                    You must login first to view your cart. <a href="../2login/1mainlogin.php" style="color:black;">Login</a>
                <?php else: ?>
                    No sneakers found in the cart.
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
    <div class="summary-table" style="max-width:580px;">
        <table style="background-color:#e6e6e6;">
            <tbody>
                <tr>
                    <td style="text-align: center;">
                        <p style="font-size: 18px;font-weight: bold;text-align:center;margin-top:10px;">
                            ORDER SUMMARY
                        </p>
                        <label style="font-size: 16px;font-weight: bold;text-align:center;padding-right:20px;">
                            Subtotal Price:
                        </label>
                        <?php echo "$" . $totalAmount; ?>
                        <label style="color:#565656;font-size: 16px;text-align:center;padding-left:0px;">
                            (Duties & taxes included)
                        </label><br><br>
                        <?php if ($loggedIn && isset($sneakersResult) && $sneakersResult->num_rows > 0): ?>
                            <button class="buy-button" onclick="checkoutIfItemsExist()">CHECKOUT</button>
                        <?php else: ?>
                            <button class="buy-button" disabled>CHECKOUT</button>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="orderauthenticity" style="display:flex; background-color:#fafafa; margin-top:10px;">
            <tbody>
                <tr>
                    <td>
                        <div style="display:flex;padding-left:20px;">
                            <img src="../icons/authenticity.png" alt="authenticity" style="max-width: 22px;min-width: 18px;height:auto;margin:0px;margin-right:6px;opacity:50%;">
                            <p style="font-size: 18px; margin: 0px; color:#696969;">
                                AUTHENTICITY
                            </p>
                        </div>
                        <p style="text-align:left;font-size:14px;padding-left:20px;padding-right:0px;color:#696969;">
                            Authenticity is the foundation of our business, and every item we sell is inspected by our expert team.
                        </p>
                    </td>
                </tr>
            </tbody>
            <tbody>
                <tr>
                    <td>
                        <div style="display:flex;padding-left:30px;">
                            <img src="../icons/in-stock.png" alt="authenticity" style="max-width: 18px;min-width: 20px;height:auto;margin:0px;margin-right:10px;opacity:50%;">
                            <p style="font-size: 18px; margin: 0px;color:#696969;">
                                READY TO SHIP
                            </p>
                        </div>
                        <p style="text-align:left;font-size:14px;padding-left:40px;padding-right:20px;color:#696969;">
                            We hold and authenticate inventory on site. Processing and order verification typically occur 1-3 business days prior to shipping.
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <table class="orderauthenticity" style="display:flex; background-color:#fafafa; margin-top:10px;">
            <tbody>
                <tr>
                    <td>
                        <div style="display:flex;padding-left:20px;">
                            <img src="../icons/handle-with-care.png" alt="authenticity" style="max-width: 22px;min-width: 18px;height:auto;margin:0px;margin-right:6px;opacity:50%;">
                            <p style="font-size: 18px; margin: 0px; color:#696969;">
                                PACKAGING
                            </p>
                        </div>
                        <p style="text-align:left;font-size:14px;padding-left:20px;padding-right:0px;color:#696969;">
                            We ensure secure packaging and thorough handling for every order. Each item is carefully wrapped, inspected, and shipped with insurance and tracking for peace of mind.
                        </p>
                    </td>
                </tr>
            </tbody>
            <tbody>
                <tr>
                    <td>
                        <div style="display:flex;padding-left:10px;">
                            <img src="../icons/return-box.png" alt="authenticity" style="max-width: 18px;min-width: 20px;height:auto;margin:0px;margin-right:10px;opacity:50%;">
                            <p style="font-size: 18px; margin: 0px;color:#696969;">
                                RETURN & EXCHANGE
                            </p>
                        </div>
                        <p style="text-align:left;font-size:14px;padding-left:20px;padding-right:20px;color:#696969;">
                            Our Return and Exchange Policy allows you to return or exchange items within 7 days of receipt for a full refund or exchange. Simply contact our customer service team to initiate the process.
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const removeButtons = document.querySelectorAll(".remove-item");

        removeButtons.forEach(button => {
            button.addEventListener("click", function() {
                const cartId = this.getAttribute("data-cartid");
                removeItemFromCart(cartId);
            });
        });

        function removeItemFromCart(cartId) {
            fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cartid=${cartId}`
            })
            .then(response => response.text())
            .then(data => {
                console.log(data);
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });

    function checkoutIfItemsExist() {
        var cartItemsExist = <?php echo ($sneakersResult->num_rows > 0) ? 'true' : 'false'; ?>;
        
        if (cartItemsExist) {
            window.location.href = '2cart.php';
        } else {
            alert('Your cart is empty. Please add items before proceeding to checkout.');
        }
    }
</script>
