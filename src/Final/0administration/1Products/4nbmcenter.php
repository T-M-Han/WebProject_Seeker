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

    <title>Product Management Center</title>
    <script src="main.js" defer></script>
</head>
<body>
    <header>
        <div class="header-text" style="display:flex;justify-content:space-between">
            <br>
            <p>
                <a style="color: inherit; text-decoration: none;">
                    SEEKER - SNEAKER INVENTORY
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
        <a href="1jmcenter.php">JORDAN</a>
        <a href="2nmcenter.php">NIKE</a>
        <a href="3ymcenter.php">YEEZY</a>
        <a href="4nbmcenter.php">NEW BALANCE</a>
        <a href="5amcenter.php">ADIDAS</a>
    </nav>

    <div class="container0">
        <div class="insert-button-container">
            <button onclick="openPopupForm()" class="insert-button">INSERT NEW SNEAKER</button>
        </div>

        <div id="popupFormContainer" class="popup-form-container">
            <div class="popup-form">
                <span class="close" onclick="closePopupForm()">&times;</span>
                <h2>Insert New Sneaker</h2>
                <form id="insertForm" action="insert.php" method="POST" enctype="multipart/form-data">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required><br><br>

                    <label for="brand">Brand:</label>
                    <select id="brand" name="brand" onchange="checkBrand()" required>
                        <option value="" disabled selected>Select Brand</option>
                        <option value="JORDAN">JORDAN</option>
                        <option value="NIKE">NIKE</option>
                        <option value="YEEZY">YEEZY</option>
                        <option value="NEW BALANCE">NEW BALANCE</option>
                        <option value="ADIDAS">ADIDAS</option>
                    </select>
                    <input type="text" id="newBrandInput" name="newBrandInput" style="display: none;" placeholder="Enter New Brand Name"><br>

                    <label for="nickname">Nickname:</label>
                    <input type="text" id="nickname" name="nickname" required><br><br>
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" style="width:100%;height:200px;resize:none;" required></textarea><br><br>
                    <label for="colorway">Colorway:</label>
                    <input type="text" id="colorway" name="colorway" required><br><br>
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender">
                        <option value="Mens">Mens</option>
                        <option value="Womens">Womens</option>
                        <option value="Kids">Kids</option>
                    </select><br>
                    <label for="releasedate">Release Date:</label>
                    <input type="date" id="releasedate" name="releasedate" required max="<?php echo date('Y-m-d'); ?>">     

                    <label for="image">Image:</label>
                    <input type="file" id="image" name="image" required accept="image/*">
                    <br><br>

                    <label for="sizes">Sizes:</label><br>
                    <?php
                    $mysqli = new mysqli("localhost", "root", "", "seekerdb");
                    if ($mysqli->connect_errno) {
                        echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                        exit();
                    }
                    $result = $mysqli->query("SELECT * FROM sizes");
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {

                        echo '<div style="display: flex; align-items: center; margin-bottom: 10px;">';
                        echo '<label for="size_' . $row['sizeid'] . '"> EU ' . $row['sizename'] . ':</label>';
                        echo '<input type="number" id="size_' . $row['sizeid'] . '_quantity" name="size_' . $row['sizeid'] . '_quantity" min="0" placeholder="QUANTITY" required style="flex: 1; margin-left: 10px;">';
                        echo '<input type="number" id="size_' . $row['sizeid'] . '_price" name="size_' . $row['sizeid'] . '_price" min="0" step="0.01" placeholder="PRICE IN DOLLAR($)" required style="flex: 1; margin-left: 10px;">';
                        echo '</div>';
                        }
                    }
                    $mysqli->close();
                    ?>
                    <br>

                    <button type="submit">Submit</button>
                </form>
            </div>
        </div>

        <div id="confirmationPopup" class="confirmation-popup">
            <p>Are you sure you want to delete this sneaker?</p>
            <button id="confirmYes">Yes</button>
            <button id="confirmNo">No</button>
        </div>
        <div id="overlay"></div>
        <script>
            function showConfirmationPopup() {
                var overlay = document.getElementById('overlay');
                var popup = document.getElementById('confirmationPopup');

                overlay.style.display = 'block';
                popup.style.display = 'block';

                document.getElementById('confirmYes').onclick = function() {
                    var sneakerId = document.querySelector('[name="sneakerid"]').value;

                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'deleteprocess.php';

                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'sneakerid';
                    input.value = sneakerId;
                    form.appendChild(input);

                    document.body.appendChild(form);
                    form.submit();

                    overlay.style.display = 'none';
                    popup.style.display = 'none';
                };
                document.getElementById('confirmNo').onclick = function() {
                    overlay.style.display = 'none';
                    popup.style.display = 'none';
                };
            }
        </script>

        <div class="search-container">
            <form action="" method="GET" style="padding:0px;">
                <input type="text" name="search" placeholder="Search by ID, Name, Nickname">
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
                        <th>Sneaker</th>
                        <th>Name</th>
                        <th>Nickname</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $mysqli = new mysqli("localhost", "root", "", "seekerdb");
                    if ($mysqli->connect_errno) {
                        echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                        exit();
                    }

                    $sneakerbrand = "NEW BALANCE";
                    $sneakerQuery = "SELECT * FROM sneakers WHERE brand='$sneakerbrand'";

                    if (isset($_GET['search']) && !empty($_GET['search'])) {
                        $searchTerm = $_GET['search'];
                        $sneakerQuery .= " AND (sneakerid LIKE '%$searchTerm%' OR name LIKE '%$searchTerm%' OR nickname LIKE '%$searchTerm%')";
                    }

                    $result = $mysqli->query($sneakerQuery);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . $row['sneakerid'] . '</td>';
                            echo '<td><img src="data:image/jpeg;base64,' . base64_encode($row['image']) . '" alt="' . $row['name'] . '"></td>';
                            echo '<td>' . $row['name'] . '</td>';
                            echo '<td>' . $row['nickname'] . '</td>';
                            echo '<td><span class="toggle-details" style="color:black;" data-sneakerid="' . $row['sneakerid'] . '">Details</span></td>';
                            echo '</tr>';
                            echo '<tr class="sneaker-details-row" id="sneakerDetailsRow_' . $row['sneakerid'] . '" style="display: none;">';
                            echo '<td colspan="5" style="padding:0px;"><div class="sneaker-details-container" id="sneakerDetailsContainer_' . $row['sneakerid'] . '"></div></td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5">No sneakers found.</td></tr>';
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