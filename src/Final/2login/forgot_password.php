<?php
session_start();

$loggedIn = isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true;

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

$email = "";
$securityQuestion = "";
$error = "";
$securityAnswerCorrect = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $mysqli = new mysqli("localhost", "root", "", "seekerdb");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: " . $mysqli->connect_error;
        exit();
    }

    $query = "SELECT security_question FROM customer WHERE email = '$email'";
    $result = $mysqli->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $securityQuestion = $row['security_question'];
    } else {
        $error = "Email not found.";
    }

    if (isset($_POST['security_answer'])) {
        $securityAnswer = $_POST['security_answer'];

        $query = "SELECT * FROM customer WHERE BINARY email = '$email' AND BINARY security_answer = '$securityAnswer'";
        $result = $mysqli->query($query);

        if ($result->num_rows > 0) {
            $securityAnswerCorrect = true;
        } else {
            $error = "Incorrect security answer.";
        }
    }

    if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        $query = "SELECT password FROM customer WHERE email = '$email'";
        $result = $mysqli->query($query);
        $row = $result->fetch_assoc();
        $oldPassword = $row['password'];

        if ($newPassword !== $confirmPassword) {
            $error = "Passwords do not match.";
        } elseif ($newPassword === $oldPassword) {
            $error = "New password cannot be the same as the old password.";
        } else {
            $updateQuery = "UPDATE customer SET password = '$newPassword' WHERE email = '$email'";
            if ($mysqli->query($updateQuery)) {
                $successMessage = "Password updated successfully.";
                header("Location: 1mainlogin.php");
                exit;
            } else {
                $error = "Error updating password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="icon" href="../images/SK.logo.png">
    <title>SEEKER-Sneaker Store - Forgot Password</title>
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

    <?php if ($error === ""): ?>
    <div class="forgot-password-container">
        <?php if ($securityQuestion !== ""): ?>
            <?php if ($securityAnswerCorrect): ?>
                <h2>Change Password</h2>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validatePassword()">
                    <p id="password-error" style="color: red;font-size: 12px;text-align: center;margin-bottom: 10px;"></p>
                    <div class="input-group">
                        <input type="password" id="new_password" name="new_password" placeholder="New Password" required>
                    </div>
                    <div class="input-group">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                        <span class="show-password" onclick="togglePasswordVisibility2()">Show Password</span>
                    </div>
                    <input type="hidden" name="email" value="<?php echo $email; ?>">
                    <div class="input-group">
                        <button type="submit" class="submit-button">CHANGE PASSWORD</button>
                    </div>
                    <div class="input-group">
                        <button type="button" class="submit-button" style="padding: 5px 10px; font-size: 12px; background-color:white; color:black;" onclick="window.history.back();">BACK</button>
                    </div>
                </form>
            <?php else: ?>
                <h2>Security Question</h2>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="input-group">
                        <input type="text" id="security_answer" name="security_answer" placeholder="<?php echo $securityQuestion; ?>" required>
                    </div>
                    <input type="hidden" name="email" value="<?php echo $email; ?>">
                    <div class="input-group">
                        <button type="submit" class="submit-button">SUBMIT</button>
                    </div>
                    <div class="input-group">
                        <button type="button" class="submit-button" style="padding: 5px 10px; font-size: 12px; background-color:white; color:black;" onclick="history.back();">BACK</button>
                    </div>
                </form>
            <?php endif; ?>
            <?php else: ?>
                <h2>FORGOT PASSWORD</h2>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="input-group">
                        <input type="email" id="email" name="email" placeholder="Enter your email to change password!" value="<?php echo $email; ?>" required>
                    </div>
                    <div class="input-group">
                        <button type="submit" class="submit-button">ENTER</button>
                    </div>
                    <div class="input-group">
                        <button type="submit" class="submit-button" style="padding: 5px 10px; font-size: 12px; background-color:white; color:black;" onclick="window.location.href='1mainlogin.php';">BACK</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="forgot-password-container">
            <p class="error-message"><?php echo $error; ?></p>
            <div class="input-group">
                <button type="button" class="submit-button" style="padding: 5px 10px; font-size: 12px; background-color:white; color:black;" onclick="window.location.href='forgot_password.php';">Try Again</button>
            </div>
        </div>
    <?php endif; ?>

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
