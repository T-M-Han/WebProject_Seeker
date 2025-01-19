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
                <input type="text" name="search" placeholder="Search by ID, Order ID, Rating, Date" value="<?php echo isset($_GET['search']) ? $_GET['search'] : '' ?>">
                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="table-container1">
            <table class="table1">
                <thead>
                    <tr>
                        <th>Review ID</th>
                        <th>Customer ID</th>
                        <th>Order ID</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $mysqli = new mysqli("localhost", "root", "", "seekerdb");
                    if ($mysqli->connect_errno) {
                        echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                        exit();
                    }

                    $sql = "SELECT `reviewid`, `customerid`, `orderid`, `rating`, `comment`, `date` FROM `review`";

                    if (isset($_GET['search']) && !empty($_GET['search'])) {
                        $searchTerm = $_GET['search'];
                        $sql .= " WHERE (`customerid` LIKE '%$searchTerm%' OR `orderid` LIKE '%$searchTerm%' OR `rating` LIKE '%$searchTerm%' OR `date` LIKE '%$searchTerm%')";
                    }
                    $sql .= " ORDER BY `rating` ASC";

                    $result = $mysqli->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row["reviewid"] . "</td>";
                            echo "<td>" . $row["customerid"] . "</td>";
                            echo "<td>" . $row["orderid"] . "</td>";
                            echo "<td>" . $row["rating"] . "</td>";
                            echo "<td>" . $row["comment"] . "</td>";
                            echo "<td>" . $row["date"] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No feedbacks found</td></tr>";
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
