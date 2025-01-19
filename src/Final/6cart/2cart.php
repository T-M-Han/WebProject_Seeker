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

if ($loggedIn && isset($_SESSION['customerid'])) {
    $customerId = $_SESSION['customerid'];

    $sneakersQuery = "SELECT cart.cartid, sneakers.*, sizes.*, prices.price
                      FROM cart
                      JOIN sneakers ON cart.sneakerid = sneakers.sneakerid
                      JOIN sizes ON cart.sizeid = sizes.sizeid
                      JOIN prices ON cart.sneakerid = prices.sneakerid AND cart.sizeid = prices.sizeid
                      WHERE cart.customerid = $customerId";

    $sneakersResult = $mysqli->query($sneakersQuery);

    if ($sneakersResult && $sneakersResult->num_rows > 0) {
        $_SESSION['cart'] = array();
        $totalAmount = 0;

        while ($row = $sneakersResult->fetch_assoc()) {
            $item = array(
                'sneakerid' => $row['sneakerid'],
                'name' => $row['name'],
                'nickname' => $row['nickname'],
                'image' => $row['image'],
                'sizeid' => $row['sizeid'],
                'sizename' => $row['sizename'],
                'price' => $row['price']
            );

            $_SESSION['cart'][] = $item;
            $totalAmount += $row['price'];
        }
    } else {
        $_SESSION['cart'] = array();
        $totalAmount = 0;
    }

    $sneakersResult->free();
} else {
    $_SESSION['cart'] = array();
    $totalAmount = 0;
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
            <a href="" title="Shopping Cart" style="text-decoration: none;">
                <img src="../icons/cart.png" alt="Shopping Cart">
                <span id="cartItemCount"><?php echo $cartItemCount; ?></span>
            </a>
        <?php else: ?>
            <a href="" title="Shopping Cart">
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
    CHECKOUT PROCESS
</p>
<div class="orderinfo" style="display: flex;justify-content: center;">
    <div class="cartcontainer">
        <?php

        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "seekerdb";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $errorMessages = array();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $email = $_POST['email'];
            $address = $_POST['address'];
            $city = $_POST['city'];
            $country = $_POST['country'];
            $phone = $_POST['phone'];

            $isValid = validateForm();

            if ($isValid) {
                $customerid = $_SESSION['customerid'];
                $sql = "UPDATE customer SET firstname=?, lastname=?, email=?, address=?, city=?, country=?, phone=? WHERE customerid=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssi", $firstname, $lastname, $email, $address, $city, $country, $phone, $customerid);

                if ($stmt->execute()) {
                    header("Location: 2cart.php");
                    exit;
                } else {
                    $errorMessages['database'] = "Error updating customer information: " . $conn->error;
                }
            } else {
                $errorMessages['validation'] = "Please check Email or Phone again!";
            }
        }

        if(isset($_SESSION['customerid'])) {
            $customerid = $_SESSION['customerid'];
            $sql = "SELECT firstname, lastname, email, address, city, country, phone FROM customer WHERE customerid = $customerid";
            $result = $conn->query($sql);

            if($result) {
                $row = $result->fetch_assoc();
                echo "<div>";
                echo "<table style='background-color:#fafafa;'>";
                echo "<tbody style='display:block; padding:13px;'>";
                echo "<tr><td colspan='4'><p style='font-size: 18px;margin: 0px; text-align:center;'>SHIPPING INFORMATION</p></td></tr>";
                echo "<tr><td>First Name:</td><td>" . $row['firstname'] . "</td></tr>";
                echo "<tr><td>Last Name:</td><td>" . $row['lastname'] . "</td></tr>";
                echo "<tr><td>Email:</td><td>" . $row['email'] . "</td></tr>";
                echo "<tr><td>Address:</td><td>" . $row['address'] . "</td></tr>";
                echo "<tr><td>City:</td><td>" . $row['city'] . "</td></tr>";
                echo "<tr><td>Country:</td><td>" . $row['country'] . "</td></tr>";
                echo "<tr><td>Phone:</td><td>" . $row['phone'] . "</td>";
                echo "<td><button class='remove-item' onclick='openEditForm()' style='background-color:#fafafa;padding-left:50px;'>EDIT</button></td>";
                echo "<tbody>";
                echo "</table>";
                echo "</div>";

                echo "<div id='editFormPopup' class='popup' style='" . (isset($errorMessages['validation']) ? "display: block;" : "display: none;") . "'>";
                echo "<h2>Edit SHIPPING Information</h2>";
                echo "<form id='editForm' method='post'>";
                if(isset($errorMessages['validation']))
                    echo "<span class='error-message'>" . $errorMessages['validation'] . "</span><br>";
                echo "<input type='text' name='firstname' id='firstname' placeholder='First Name' value='" . $row['firstname'] . "'required><br>";
                if(isset($errorMessages['firstname']))
                    echo "<span class='error-message'>" . $errorMessages['firstname'] . "</span><br>";
                echo "<input type='text' name='lastname' id='lastname' placeholder='Last Name' value='" . $row['lastname'] . "'required><br>";
                if(isset($errorMessages['lastname']))
                    echo "<span class='error-message'>" . $errorMessages['lastname'] . "</span><br>";
                echo "<input type='email' name='email' id='email' placeholder='Email' value='" . $row['email'] . "'required><br>";
                if(isset($errorMessages['email']))
                    echo "<span class='error-message'>" . $errorMessages['email'] . "</span><br>";
                echo "<input type='text' name='address' id='address' placeholder='Address' value='" . $row['address'] . "'required><br>";
                if(isset($errorMessages['address']))
                    echo "<span class='error-message'>" . $errorMessages['address'] . "</span><br>";
                echo "<input type='text' name='city' id='city' placeholder='City' value='" . $row['city'] . "'required><br>";
                if(isset($errorMessages['city']))
                    echo "<span class='error-message'>" . $errorMessages['city'] . "</span><br>";
                echo "<input type='text' name='country' id='country' placeholder='Country' value='" . $row['country'] . "'required><br>";
                if(isset($errorMessages['country']))
                    echo "<span class='error-message'>" . $errorMessages['country'] . "</span><br>";
                echo "<input type='text' name='phone' id='phone' placeholder='Phone' value='" . $row['phone'] . "'required><br>";
                if(isset($errorMessages['phone']))
                    echo "<span class='error-message'>" . $errorMessages['phone'] . "</span><br>";
                echo "<p style='font-size:12px;'>Remark: This information will be updated to your account information.</p>";
                echo "<button type='submit' class='buy-button' >UPDATE</button>";
                echo "</form>";
                echo "<button onclick='closeEditForm()' style='background-color:white; color:black;'>Cancel</button>";
                echo "</div>";

            } else {
                echo "Error retrieving customer data: " . $conn->error;
            }
        } else {
            echo "Customer ID not set in session.";
        }

        $conn->close();

        function validateForm() {
            $isValid = true;

            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $email = $_POST['email'];
            $address = $_POST['address'];
            $city = $_POST['city'];
            $country = $_POST['country'];
            $phone = $_POST['phone'];

            $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';
            if (!preg_match($emailRegex, $email) || !strpos($email, '@gmail.com')) {
                $isValid = false;
                $errorMessages['email'] = "Please enter a valid Gmail address.";
            }

            $phoneRegex = '/^\d{11}$/';
            if (!preg_match($phoneRegex, $phone)) {
                $isValid = false;
                $errorMessages['phone'] = "Please enter a valid phone number with 11 digits and no letters.";
            }

            if ($firstname === "") {
                $isValid = false;
                $errorMessages['firstname'] = "Please enter your first name.";
            }
            if ($lastname === "") {
                $isValid = false;
                $errorMessages['lastname'] = "Please enter your last name.";
            }
            if ($address === "") {
                $isValid = false;
                $errorMessages['address'] = "Please enter your address.";
            }
            if ($city === "") {
                $isValid = false;
                $errorMessages['city'] = "Please enter your city.";
            }
            if ($country === "") {
                $isValid = false;
                $errorMessages['country'] = "Please enter your country.";
            }

            return $isValid;
        }
        ?>

        <script>
        function openEditForm() {
            document.getElementById('editFormPopup').style.display = 'block';
            document.body.classList.add('modal-open');
        }

        function closeEditForm() {
            document.getElementById('editFormPopup').style.display = 'none';
            document.body.classList.remove('modal-open');
        }
        </script>

        <style>
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            z-index: 9999;
            max-width: 400px;
        }

        .popup h2 {
            margin-top: 0;
            color: #333;
        }

        .popup input[type="text"],
        .popup input[type="email"],
        .popup button[type="submit"] {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .popup button[type="submit"],
        .popup button {
            display: block;
            width: 100%;
            padding: 12px;
            font-size: 16px;
            color: #fff;
            background-color: #000000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .popup button[type="submit"]:hover,
        .popup button:hover {
            background-color: #555555;
        }

        .popup .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 5px;
        }

        body.modal-open {
            overflow: hidden;
        }

        body.modal-open::after {
            content: "";
            background-color: rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9998;
        }
        </style>
    <div class="payment">
        <div style="margin-top:10px;">
            <table style="background-color:#fafafa; ">
                <tbody>
                    <tr>
                        <td colspan='5'>           
                            <p style="font-size: 18px;text-align:center;">
                                PAYMENT OPTIONS
                            </p>
                        </td>
                    </tr>
                    <?php
                    $mysqli = new mysqli("localhost", "root", "", "seekerdb");
                    if ($mysqli->connect_errno) {
                        echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                        exit();
                    }

                    $paymentMethodsQuery = "SELECT `methodid`, `paymentmethod`, `image`, `accountname`, `company` FROM `paymentmethods`";
                    $paymentMethodsResult = $mysqli->query($paymentMethodsQuery);

                    $firstRow = true;

                    while ($row = $paymentMethodsResult->fetch_assoc()):
                    ?>
                    <tr style="background-color: #f9f9f9; border-bottom: 1px solid #ddd;">
                        <td style="padding: 10px;">
                            <input type="radio" name="payment_method" value="<?php echo $row['paymentmethod']; ?>" <?php echo ($firstRow ? 'checked' : ''); ?>>
                        </td>
                        <td style="text-align:center; padding: 10px;"><h4 style="margin: 0;"><?php echo $row['paymentmethod']; ?></h4></td>
                        <td style="padding: 10px;">
                            <?php if (!empty($row['company'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['company']); ?>" alt="Payment Method Image" style="width:100%; min-width:0px; max-width: 60px; max-height:60px;">
                            <?php endif; ?>
                        </td>
                        <td style="padding: 10px;">
                            <?php if (!empty($row['image'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>" alt="Payment Method Image" style="width:100%; min-width:0px; max-width: 60px; max-height:60px;">
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center; padding: 10px;"><?php echo $row['accountname']; ?></td>
                    </tr>
                    <?php 
                    $firstRow = false;
                    endwhile; ?>

                    <?php
                    $mysqli->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>

    <div class="cartcontainer" style="max-width:580px;">
        <div class="summary-table">
            <table style="background-color:#e6e6e6;">
                <tbody>
                    <tr>
                        <td style="text-align: center;padding-top:0px;">
                            <p style='font-size: 18px;font-weight: bold;text-align:center;'>
                                ORDER SUMMARY
                            </p>
                            <label style="font-size: 16px;font-weight: bold;text-align:center;">
                                Subtotal Price:
                            </label>
                            <?php echo "$" . $totalAmount; ?>
                            <label style="color:#565656;font-size: 16px;text-align:center;padding-left:0px;">
                                (Duties & taxes included)
                            </label><br><br>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php if ($loggedIn && isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
            <div class="cart-table" style="padding-top:10px;">
                <table>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <tr>
                                <td style="padding-bottom: 20px;">
                                    <img src='data:image/jpeg;base64,<?php echo base64_encode($item['image']); ?>' alt='Sneaker'>
                                </td>
                                <td>
                                    <label style="font-size: 16px;font-weight: bold;padding-right:20px;">
                                        <?php echo $item['name']; ?><br>
                                    </label>
                                    <label style="font-size: 14px;padding-right:20px;">
                                        "<?php echo $item['nickname']; ?>"<br> 
                                    </label>
                                    <label style="font-size: 12px;padding-right:20px;">
                                        Size: EU <?php echo $item['sizename']; ?><br>
                                    </label>
                                </td>
                                <td>
                                    <?php echo "$" . $item['price']; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align:center;">No sneakers found in the cart.</p>
        <?php endif; ?>   
        <?php
        if(isset($_GET['errorMsg'])) {
            $errorMsg = urldecode($_GET['errorMsg']);
            echo '<div class="error-message" style="color:red; text-align:center; margin:10px;">' . $errorMsg . '</div>';
        }
        ?>

        <button id="checkoutButton" class="buy-button">CHECKOUT</button>  
        <table class="orderauthenticity" style="display:flex; background-color:#fafafa; margin-top:1x`x`0px;">
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
                        <div style="display:flex;padding-left:20px;">
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
    <script>
    document.getElementById('checkoutButton').addEventListener('click', function(event) {
        event.preventDefault();

        var paymentMethod = document.querySelector('input[name="payment_method"]:checked');

        if (paymentMethod) {
            document.getElementById('paymentMethodInput1').value = paymentMethod.value;
            document.getElementById('paymentMethodInput2').value = paymentMethod.value;
            
            checkInventory(paymentMethod.value);
        } else {
            alert('Please select a payment method.');
        }
    });

    function checkInventory(paymentMethod) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'check_inventory.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    var response = xhr.responseText;
                    if (response === 'success') {
                        if (paymentMethod !== 'Cash on Delivery') {
                            openSerialNumberForm();
                        } else {
                            openCODForm();
                        }
                    } else {
                        window.location.href = '2cart.php?errorMsg=' + encodeURIComponent(response);
                    }
                } else {
                    alert('Error checking inventory: ' + xhr.status);
                }
            }
        };
        var params = 'payment_method=' + encodeURIComponent(paymentMethod);
        xhr.send(params);
    }

    function openSerialNumberForm() {
        document.getElementById('serialNumberFormPopup').style.display = 'block';
        document.body.classList.add('modal-open');
    }
    function closeSerialNumberForm() {
        document.getElementById('serialNumberFormPopup').style.display = 'none';
        document.body.classList.remove('modal-open');
    }

    function openCODForm() {
        document.getElementById('CODFormPopup').style.display = 'block';
        document.body.classList.add('modal-open');
    }
    function closeCODForm() {
        document.getElementById('CODFormPopup').style.display = 'none';
        document.body.classList.remove('modal-open');
    }
    </script>

    <div id="serialNumberFormPopup" class="popup" style="display: none;">
        <h2>Enter Transaction Serial Number</h2>
        <form id="serialNumberForm" method="post" action="process_checkout.php">
            <input type="hidden" name="payment_method" id="paymentMethodInput1">
            <input type="hidden" name="total_amount" id="totalAmountInput" value="<?php echo $totalAmount; ?>">
            <input type="text" name="serial_number" id="serialNumber" placeholder="Transaction Serial Number" required><br>
            <button id="checkoutButton2" type="submit" class="buy-button">CONFIRM CHECKOUT</button>
        </form> 
        <button onclick="closeSerialNumberForm()" style="background-color:white; color:black;">Cancel</button>
    </div>

    <div id="CODFormPopup" class="popup" style="display: none;">
        <h2>YOUR PACKAGE WILL BE ON THE PROCESS!</h2>
        <label><strong>Important Notice:</strong> Delivery fees are not included in your order total. Payment is due upon delivery by the courier. Please ensure someone is available to receive the package. Orders not accepted upon delivery will be automatically canceled.</label>
        <br><br><label>Thank you for your cooperation!</label>

        <form id="CODForm" method="post" action="process_checkout.php">
            <input type="hidden" name="payment_method" id="paymentMethodInput2" >
            <input type="hidden" name="total_amount" id="totalAmountInput"  value="<?php echo $totalAmount; ?>">
            <input type="hidden" name="serial_number" id="serialNumber"><br>
            <button  id="checkoutButton3" type="submit" class="buy-button">CONFIRM CHECKOUT</button>
        </form>
        <button onclick="closeCODForm()" style="background-color:white; color:black;">Cancel</button>
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
