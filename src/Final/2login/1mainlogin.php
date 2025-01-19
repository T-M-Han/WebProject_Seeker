<?php
session_start();

if (isset($_GET['error_message'])) {
    $error_message = urldecode($_GET['error_message']);
}

$cartItemCount = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "seekerdb";

        $input_email = $_POST['email'];
        $input_password = $_POST['password'];

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM customer WHERE BINARY email = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $input_email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();

                if ($row['block'] == 1) {
                    $error_message = "Your account has been blocked due to the policy of our company. Please contact support for assistance or create new account.";
                } else {
                    if ($input_password == $row['password']) {

                        $_SESSION['customerid'] = $row['customerid'];
                        $_SESSION['loggedIn'] = true;

                        header("Location: ../3main/1new.php");
                        exit();
                    } else {
                        $error_message = "Invalid password. Please try again.";
                    }
                }

            } else {
                $error_message = "Email not found. Please try again.";
            }
        } else {
            $error_message = "SQL statement preparation error.";
        }
        $stmt->close();
        $conn->close();
    } else {
        $error_message = "Both email and password are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="icon" href="../images/SK.logo.png">
    <title>SEEKER-Sneaker Store</title>
    <link rel="stylesheet" href="login.css">
    <script src="login.js" defer></script>
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
            <a href="1mainlogin.php" title="Profile" style="padding-left:5px;"><img src="../icons/profile.png" alt="Profile"></a>
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

    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form  method="POST" name="loginForm" onsubmit="return validateForm()">
            <div class="input-group">
                <span id="email-error" class="error-message"></span>
                <input type="text" id="email" name="email" placeholder="Email">
            </div>
            <div class="input-group">
                <?php if (isset($perror_message)): ?>
                    <div class="error-message"><?php echo $perror_message; ?></div>
                <?php endif; ?>
                <span id="password-error" class="error-message"></span>
                <input type="password" id="password" name="password" placeholder="Password">
                <span class="show-password" onclick="togglePasswordVisibility()">Show Password</span>
            </div>
            <div class="input-group">
                <button class="login-button" name="loginbtn" type="submit">Login</button>
            </div>
            <div class="options">
                <a href="forgot_password.php">Forgot Password?</a>
                <span> | </span>
                <a href="../1signup/mainsignup.php">Create an Account</a>
            </div>
        </form>
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
