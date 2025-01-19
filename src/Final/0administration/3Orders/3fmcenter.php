<?php
session_start();
if (!isset($_SESSION["loggedIn"])) {
    header("Location: ../0login/adminlogin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="icon" href="../../images/SK.logo.png">
    <link rel="stylesheet" href="main.css">
    <title>Orders Management Center</title>
</head>
<body>
    <header>
        <div class="header-text" style="display:flex;justify-content:space-between;">
            <br>
            <p>
                <a style="color: inherit; text-decoration: none;">
                    ORDERS MANAGEMENT CENTER
                </a>
            </p>
            <button class="logout-btn" onclick="logout()">LOGOUT</button>
            <script>
            function logout() {
                window.location.href = 'logout.php';
            }
            </script>
        </div>
        <button class="menu-toggle" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </header>
    <nav>
        <a href="3fmcenter.php">ORDERS LIST</a>
    </nav>
    <div class="container0">
        <div class="payment-method-filter">
            <form action="" method="GET" style="padding:0px;">
                <select id="payment-method-filter" name="payment_method">
                    <option value="">All Payment Methods</option>
                    <option value="Cash On Delivery"<?php if(isset($_GET['payment_method']) && $_GET['payment_method'] === 'Cash On Delivery') echo ' selected'; ?>>Cash On Delivery</option>
                    <option value="WavePay"<?php if(isset($_GET['payment_method']) && $_GET['payment_method'] === 'WavePay') echo ' selected'; ?>>WavePay</option>
                    <option value="KBZPay"<?php if(isset($_GET['payment_method']) && $_GET['payment_method'] === 'KBZPay') echo ' selected'; ?>>KBZPay</option>
                    <option value="AYAPay"<?php if(isset($_GET['payment_method']) && $_GET['payment_method'] === 'AYAPay') echo ' selected'; ?>>AYAPay</option>
                </select>
                <script>
                    document.getElementById('payment-method-filter').addEventListener('change', function() {
                        var selectedPaymentMethod = this.value;
                        var url = window.location.href.split('?')[0];
                        var params = new URLSearchParams(window.location.search);

                        if (selectedPaymentMethod !== '') {
                            params.set('payment_method', selectedPaymentMethod);
                        } else {
                            params.delete('payment_method');
                        }

                        window.location.href = url + '?' + params.toString();
                    });
                </script>
            </form>
        </div>
        <div class="status-filter">
            <form action="" method="GET" style="padding:0px;">
                <select id="status-filter">
                    <option value="">All Status</option>
                    <option value="Cancelled"<?php if(isset($_GET['status']) && $_GET['status'] === 'Cancelled') echo ' selected'; ?>>Cancelled</option>
                    <option value="Processing"<?php if(isset($_GET['status']) && $_GET['status'] === 'Processing') echo ' selected'; ?>>Processing</option>
                    <option value="Pending"<?php if(isset($_GET['status']) && $_GET['status'] === 'Pending') echo ' selected'; ?>>Pending</option>
                    <option value="Delivering"<?php if(isset($_GET['status']) && $_GET['status'] === 'Delivering') echo ' selected'; ?>>Delivering</option>
                    <option value="Delivered"<?php if(isset($_GET['status']) && $_GET['status'] === 'Delivered') echo ' selected'; ?>>Delivered</option>
                </select>
                <script>
                    document.getElementById('status-filter').addEventListener('change', function() {
                        var selectedOption = this.value;
                        var url = window.location.href.split('?')[0];
                        if (selectedOption !== '') {
                            url += '?status=' + encodeURIComponent(selectedOption);
                        }
                        window.location.href = url;
                    });
                </script>
            </form>
        </div>
        <div class="search-container">
            <form action="" method="GET" style="padding:0px;">
                <input type="text" name="search" placeholder="Search by Order ID, Customer Id, Order Date, Payment Method, Status">
                <button type="submit">Search</button>
            </form>
        </div>
    </div>
    <div class="container">
        <div class="table-container1">
            <div class="table">
                <div class="row">
                    <div class="cell">
                        <div class="orders" style="text-align: center;padding:0px;">
                        <?php
                            $mysqli = new mysqli("localhost", "root", "", "seekerdb");
                            if ($mysqli->connect_errno) {
                                echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                                exit();
                            }
                            $ordersQuery = "SELECT `orderid`, `customerid`, `orderdate`, `paymethod`, `transactionno`, `totalamount`, `status` FROM `orders` WHERE 1=1";
                            if (isset($_GET['search']) && !empty($_GET['search'])) {
                                $searchTerm = $_GET['search'];
                                $ordersQuery .= " AND (`orderid` LIKE '%$searchTerm%' OR `customerid` LIKE '%$searchTerm%' OR `orderdate` LIKE '%$searchTerm%' OR `paymethod` LIKE '%$searchTerm%' OR `status` LIKE '%$searchTerm%')";
                            }
                            if (isset($_GET['status']) && !empty($_GET['status'])) {
                                $statusFilter = $_GET['status'];
                                $ordersQuery .= " AND `status` = '$statusFilter'";
                            }
                            if (isset($_GET['payment_method']) && !empty($_GET['payment_method'])) {
                                $paymentMethodFilter = $_GET['payment_method'];
                                $ordersQuery .= " AND `paymethod` = '$paymentMethodFilter'";
                            }
                            $ordersResult = $mysqli->query($ordersQuery);

                            if ($ordersResult) {
                                if ($ordersResult->num_rows > 0) {
                                    echo '<table class="responsive-table">';
                                    echo '<tr><th>Order ID</th><th>Customer ID</th><th>Order Date</th><th>Payment Method</th><th>Transaction No</th><th>Total Amount</th><th>Status</th><th>Details</th></tr>';
                                    while ($row = $ordersResult->fetch_assoc()) {
                                        $orderId = $row['orderid'];
                                        $hashedOrderId = strtoupper(md5($orderId));
                                        echo '<tr>';
                                        echo '<td>' . $orderId . '</td>';
                                        echo '<td>' . $row['customerid'] . '</td>';
                                        echo '<td>' . $row['orderdate'] . '</td>';
                                        echo '<td>' . $row['paymethod'] . '</td>';
                                        echo '<td>' . $row['transactionno'] . '</td>';
                                        echo '<td>$' . $row['totalamount'] . '</td>';
                                        echo '<td class="status-update-cell">';
                                        echo '<div class="dropdown">';
                                        echo '<button class="dropbtn">' . $row['status'] . '</button>';
                                        echo '<div class="dropdown-content">';
                                        echo '<form id="updateForm_' . $orderId . '">';
                                        echo '<input type="hidden" name="orderid" value="' . $orderId . '">';
                                        echo '<select name="newstatus" onchange="updateStatus(' . $orderId . ', this.value)">';
                                        $statusOptions = array("Cancelled", "Processing", "Pending", "Delivering", "Delivered");
                                        foreach ($statusOptions as $option) {
                                            if ($option === $row['status']) {
                                                echo '<option value="' . $option . '" selected>' . $option . '</option>';
                                            } else {
                                                echo '<option value="' . $option . '">' . $option . '</option>';
                                            }
                                        }
                                        echo '</select>';
                                        echo '</form>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</td>';
                                        echo '<td><span class="toggle-details" style="color:black;"data-orderid="' . $orderId . '">Details</span></td>';
                                        echo '</tr>';
                                        echo '<tr class="order-details-row" id="orderDetailsRow_' . $orderId . '" style="display: none;">';
                                        echo '<td colspan="8" style="padding:0px;"><div class="order-details-container" id="orderDetailsContainer_' . $orderId . '"></div></td>';
                                        echo '</tr>';
                                    }
                                    echo '</table>';
                                } else {
                                    echo 'No orders found.';
                                }
                            } else {
                                echo "Error executing the query: " . $mysqli->error;
                            }
                            $mysqli->close();
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuToggle = document.querySelector('.menu-toggle');
            const nav = document.querySelector('nav');

            menuToggle.addEventListener('click', function () {
                this.classList.toggle('open');
                nav.classList.toggle('open');
            });
        });

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

        function updateStatus(orderId, newStatus) {
            var form = document.getElementById('updateForm_' + orderId);
            var formData = new FormData(form);

            fetch('update_order_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    console.log('Status updated successfully!');
                    location.reload();
                } else {
                    console.error('Error updating status:', response.statusText);
                }
            })
            .catch(error => {
                console.error('Error updating status:', error);
            });
        }
    </script>
    <footer>
        <p>&copy; 2024 SEEKER - CONTROL CENTER. All Rights Reserved.</p>
    </footer>
</body>
</html>
