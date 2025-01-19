<?php
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

if (!isset($_GET['orderid'])) {
    header("Location: index.php");
    exit();
}

$orderID = $_GET['orderid'];
$hashedOrderID = strtoupper(substr(md5($orderID), 0, 5));
$mysqli = new mysqli("localhost", "root", "", "seekerdb");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

$orderQuery = "SELECT o.*, c.*, od.*, s.*, s.name AS productname, s.image, sz.sizename
               FROM orders o
               JOIN orderdetail od ON o.orderid = od.orderid
               JOIN sneakers s ON od.sneakerid = s.sneakerid
               JOIN sizes sz ON od.sizeid = sz.sizeid
               JOIN customer c ON o.customerid = c.customerid
               WHERE o.orderid = '$orderID'";

$orderResult = $mysqli->query($orderQuery);

if ($orderResult->num_rows > 0) {
    $order = $orderResult->fetch_assoc();
    $orderDate = $order['orderdate'];
    $totalAmount = $order['totalamount'];
    $customerName = $order['firstname'] . ' ' . $order['lastname'];
    $paymentMethod = $order['paymethod'];
    $transactionNo = $order['transactionno'];
    $orderStatus = $order['status'];
    $shippingaddress = $order['address'] . '<br>' . $order['city'] . '<br>' . $order['country'];
    $phone = $order['phone'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $rating = $_POST['rating'];
        $comment = $_POST['comment'];
        $customerID = $order['customerid'];
        $insertReviewQuery = "INSERT INTO review (customerid, orderid, rating, comment, date) VALUES (?, ?, ?, ?, NOW())";

        $stmt = $mysqli->prepare($insertReviewQuery);
        $stmt->bind_param("iiss", $customerID, $orderID, $rating, $comment);

        if ($stmt->execute()) {
        header("Location: ../3main/1new.php");
        exit();
        } else {
        echo "Error: " . $insertReviewQuery . "<br>" . $stmt->error;
        }
        $stmt->close();

    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="shortcut icon" type="icon" href="../images/SK.logo.png">
        <title>Order Receipt</title>
        <link rel="stylesheet" href="cart.css">
        <style>
        /* Your custom CSS styles here */
        .receipt-container {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .receipt {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .receipt h3 {
            color: #333;
            padding-bottom: 10px;
            text-align:center;
        }
        .order-details {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #ccc;
            margin-bottom: 20px;
        }
        .order-details > div {
            max-width: 50%;
        }
        .items-list {
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding: 10px 0;
        }
        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .item img {
            max-width: 100px;
            max-height: 100px;
            margin-right: 10px;
        }
        .item p {
            margin: 0;
        }
        .total {
            text-align: right;
            margin-top: 20px;
        }
        .review-form {
            margin-top: 30px;
            text-align: center;
        }
        .review-form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .review-form textarea {
            width: 100%;
            height: 100px;
            resize: vertical;
            margin-bottom: 20px;
        }
        .rating-stars {
            font-size: 24px;
        }
        .rating-stars span {
            cursor: pointer;
            color: #ccc;
        }
        .rating-stars span:hover,
        .rating-stars span.active {
            color: gold;
        }
        .starbutton {
            display: block;
            width: 100%;
            padding: 8px;
            font-size: 16px;
            color: #fff;
            background-color: #000000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .starbutton:hover {
            background-color: #555555;
        }
        .review-form textarea {
            width: 98%;
            height: 100px;
            resize: vertical;
            margin-bottom: 20px;
        }
        .submit-btn {
            display: block;
            width: 100%;
            padding: 8px;
            font-size: 16px;
            color: #fff;
            background-color: #000000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .submit-btn:hover {
            background-color: #555555;
        }
        </style>
    </head>
    <body>
    <div class="receipt-container">
        <div class="receipt">
            <p style="font-size:30px;font-weight:bold;font-style:italic;text-align:center;margin-top:0px;">
                SEEKER
            </p>
            <p style="font-size:20px;text-align:center;"><strong>Order ID:</strong><label style="color:red; font-weight:bold;"> #<?php echo $orderID; ?></label></p>
            <div class="order-details" style="margin-bottom:0px;">
                <div>
                    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($orderDate)); ?></p>
                    <p><strong>Customer Name:</strong> <?php echo $customerName; ?></p>
                    <p><strong>Payment Method:</strong> <?php echo $paymentMethod; ?></p>
                    <p><strong>Transaction Number:</strong> <?php echo $transactionNo; ?></p>
                    <p><strong>Status:</strong> <?php echo $orderStatus; ?></p>
                </div>
                <div>
                    <p><strong>Shipping Address</strong></p>
                    <p><?php echo $shippingaddress; ?></p>
                    <p><strong>Phone:</strong> <?php echo $phone; ?></p>
                </div>
            </div>
            <p style="font-size:13px;opacity:80%;margin-top:0px;">Your order will require 3-4 business days to process and ship. You can check the status of your order in your <a style="color:inherit;" href="../5profile/1profile.php">profile</a> under "ORDERS".</p>

            <div class="items-list">
                <table>
                    <tbody>
                        <?php
                        $orderResult->data_seek(0);
                        while ($item = $orderResult->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td style="padding-bottom: 20px;"><img src="data:image/jpeg;base64,' . base64_encode($item['image']) . '" alt="' . $item['productname'] . '"></td>';
                            echo '<td>';
                            echo '<label style="font-size: 16px;font-weight: bold;padding-right:20px;">' . $item['productname'] . '</label><br>';
                            echo '<label style="font-size: 14px;padding-right:20px;">"' . $item['nickname'] . '"</label><br>';
                            echo '<label style="font-size: 12px;padding-right:20px;">Size: EU ' . $item['sizename'] . '</label><br>';
                            echo '</td>';
                            echo '<td style="text-align:end;">' . "$" . number_format($item['unitprice'], 2) . '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="total">
                <p><strong>Total:</strong> $<?php echo number_format($totalAmount, 2); ?></p>
            </div>
        
            <div class="review-form">
                <h2>Review Your Shopping Experience</h2>
                <form method="post">
                    <div class="rating-stars">
                        <span class="star" data-value="1">&#9733;</span>
                        <span class="star" data-value="2">&#9733;</span>
                        <span class="star" data-value="3">&#9733;</span>
                        <span class="star" data-value="4">&#9733;</span>
                        <span class="star" data-value="5">&#9733;</span>
                    </div>
                    <input type="hidden" id="rating" name="rating" value="0">
                    <br>
                    <label for="comment">Comments:</label>
                    <textarea id="comment" name="comment" placeholder="Share your experience..." required></textarea><br>
                    <button class="submit-btn" type="submit">Submit Review</button>
                </form>
            </div>
            <br><button class="submit-btn" type="button" onclick="window.location.href = '../3main/1new.php';">GET MORE SNEAKERS</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const stars = document.querySelectorAll('.star');
            stars.forEach(function (star) {
                star.addEventListener('click', function () {
                    const value = this.getAttribute('data-value');
                    document.getElementById('rating').value = value;
                    stars.forEach(function (s) {
                        if (s.getAttribute('data-value') <= value) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
            });
        });
    </script>
    </body>
    </html>
    <?php
} else {
    echo "<script>window.history.back();</script>";
}

$mysqli->close();
?>
