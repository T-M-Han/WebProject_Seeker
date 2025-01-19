<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seekerdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $question = $_POST['question'];
    $answer = $_POST['answer'];

    $checkQuery = "SELECT * FROM customer WHERE email = ? OR phone = ? OR password = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("sss", $email, $phone, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if ($row['email'] == $email) {
                $error_message = "Email already exists. Please use a different email address.<br>";
                break;
            } elseif ($row['phone'] == $phone) {
                $error_message = "Phone number already exists. Please use a different phone number.<br>";
                break;
            } elseif ($row['password'] == $password) {
                $error_message = "Password already exists. Please choose a different password.<br>";
                break;
            }
        }
    }
    
    if (empty($error_message)) {
        $sql = "INSERT INTO customer (firstname, lastname, email, address, city, country, phone, password, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $firstname, $lastname, $email, $address, $city, $country, $phone, $password, $question, $answer);
        if ($stmt->execute()) {
            session_start();
            $_SESSION['loggedIn'] = true;
            $_SESSION['customerid'] = $conn->insert_id;

            header("Location: ../3main/1new.php");
            exit;
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
$conn->close();

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="icon" href="../images/SK.logo.png">
    <title>SEEKER-Sneaker Store</title>
    <link rel="stylesheet" href="signup.css">
    <script src="signup.js" defer></script>
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

    <div class="signup-form">
        <h2>Sign Up</h2>
        <form method="POST" name="signupForm" onsubmit="return validateForm()">
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <span id="firstname-error" class="error-message"></span>
            <input type="text" name="firstname" id="firstname" placeholder="First Name" value="<?php echo isset($_POST['firstname']) ? $_POST['firstname'] : ''; ?>">
            <span id="lastname-error" class="error-message"></span>
            <input type="text" name="lastname" id="lastname" placeholder="Last Name" value="<?php echo isset($_POST['lastname']) ? $_POST['lastname'] : ''; ?>">
            <span id="email-error" class="error-message"></span>
            <input type="email" name="email" id="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
            <span id="address-error" class="error-message"></span>
            <input type="text" name="address" id="address" placeholder="Address" value="<?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?>">
            <span id="city-error" class="error-message"></span>
            <input type="text" name="city" id="city" placeholder="City" value="<?php echo isset($_POST['city']) ? $_POST['city'] : ''; ?>">
            <span id="country-error" class="error-message"></span>
            <input type="text" name="country" id="country" placeholder="Country" value="<?php echo isset($_POST['country']) ? $_POST['country'] : ''; ?>">
            <span id="phone-error" class="error-message"></span>
            <input type="text" name="phone" id="phone" placeholder="Phone" value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>">
            <span id="password-error" class="error-message"></span>
            <input type="password" name="password" id="password" placeholder="Password">
            <span id="confirm-password-error" class="error-message"></span>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" style="margin:0px;">
            <span class="show-password" onclick="togglePasswordVisibility()">Show Password</span>
            <span id="question-error" class="error-message"></span>
            <select name="question" id="question">
                <option value="" disabled selected>Select Security Question</option>
                <option value="What was your childhood nickname?">What was your childhood nickname?</option>
                <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                <option value="In which city did you meet your spouse/partner?">In which city did you meet your spouse/partner?</option>
                <option value="What is the name of your favorite fictional character?">What is the name of your favorite fictional character?</option>
                <option value="What is the name of the hospital where you were born?">What is the name of the hospital where you were born?</option>
                <option value="What was the name of your first boss?">What was the name of your first boss?</option>
                <option value="What is your favorite food?">What is your favorite food?</option>
                <option value="What was the first film you saw in theaters?">What was the first film you saw in theaters?</option>
                <option value="In which year did you graduate from high school?">In which year did you graduate from high school?</option>
                <option value="What is the name of your favorite sports team?">What is the name of your favorite sports team?</option>
            </select>
            <span id="answer-error" class="error-message"></span>
            <input type="text" name="answer" id="answer" placeholder="Answer" >
            <button type="submit">Sign Up</button>
        </form>
        <div class="login-link" style="text-align: center;">
            <p>Already have an account? 
                <a href="../2login/1mainlogin.php" style="color: #666; text-decoration: none;">
                Login here
                </a>
            </p>
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
