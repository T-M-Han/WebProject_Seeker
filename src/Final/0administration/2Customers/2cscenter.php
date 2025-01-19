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
    <title>Customer Management Center</title>
    <script src="main.js" defer></script>
</head>
<body>
    <header>
        <div class="header-text" style="display:flex;justify-content:space-between;">
            <br>
            <p>
                <a style="color: inherit; text-decoration: none;">
                    CUSTOMER MANAGEMENT CENTER
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
        <a href="2cscenter.php">CUSTOMERS LIST</a>
        <a href="3feedback.php">CUSTOMER'S REVIEWS</a>
    </nav>

    <div class="container0">
        <div class="search-container">
            <form action="" method="GET" style="padding:0px;">
                <input type="text" name="search" placeholder="Search by ID, First Name, Last Name, Email">
                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="table-container1">
            <table class="table1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>Country</th>
                        <th>Phone</th>
                        <th>Password</th>
                        <th>Security Question</th>
                        <th>Security Answer</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $mysqli = new mysqli("localhost", "root", "", "seekerdb");
                if ($mysqli->connect_errno) {
                    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                    exit();
                }

                $customerQuery = "SELECT `customerid`, `firstname`, `lastname`, `email`, `address`, `city`, `country`, `phone`, `block` FROM `customer` WHERE 1";

                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $searchTerm = $_GET['search'];
                    $customerQuery .= " AND (`customerid` LIKE '%$searchTerm%' OR `firstname` LIKE '%$searchTerm%' OR `lastname` LIKE '%$searchTerm%' OR `email` LIKE '%$searchTerm%')";
                }

                $result = $mysqli->query($customerQuery);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $row['customerid'] . '</td>';
                        echo '<td>' . $row['firstname'] . '</td>';
                        echo '<td>' . $row['lastname'] . '</td>';
                        echo '<td>' . $row['email'] . '</td>';
                        echo '<td>' . $row['address'] . '</td>';
                        echo '<td>' . $row['city'] . '</td>';
                        echo '<td>' . $row['country'] . '</td>';
                        echo '<td>' . $row['phone'] . '</td>';
                        echo '<td>********</td>';
                        echo '<td>*********</td>';
                        echo '<td>*********</td>';
                        echo '<td>';
                        if ($row['block'] == 0) {
                            echo '<form action="block_customer.php" method="post">';
                            echo '<input type="hidden" name="customer_id" value="' . $row['customerid'] . '">';
                            echo '<input type="hidden" name="action" value="block">';
                            echo '<button type="submit" class="unblock-btn">Block</button>';
                            echo '</form>';
                        } else {
                            echo '<form action="block_customer.php" method="post">';
                            echo '<input type="hidden" name="customer_id" value="' . $row['customerid'] . '">';
                            echo '<input type="hidden" name="action" value="unblock">';
                            echo '<button type="submit" class="unblock-btn">Unblock</button>';
                            echo '</form>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="12">No customers found.</td></tr>';
                }

                $mysqli->close();
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 SEEKER - CONTROL CENTER. All Rights Reserved.</p>
    </footer>
</body>
</html>
