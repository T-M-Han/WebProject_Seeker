<?php
$mysqli = new mysqli("localhost", "root", "", "seekerdb");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

if (isset($_GET['sneakerid'])) {
    $sneakerid = $mysqli->real_escape_string($_GET['sneakerid']);

    $sneakerQuery = "SELECT * FROM `sneakers` WHERE `sneakerid` = '$sneakerid'";
    $result = $mysqli->query($sneakerQuery);

    if ($result->num_rows > 0) {
        $sneaker = $result->fetch_assoc();

        echo '<div class="sneaker-details">';
        echo '<h2>Sneaker Details</h2>';
        echo '<table class="responsive-table">';
        echo '<tr>';
        echo '<td class="description-cell" colspan="2"><strong>Description:</strong> ' . $sneaker['description'] . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td><strong>Brand:</strong> ' . $sneaker['brand'] . '</td>';
        echo '<td><strong>Gender:</strong> ' . $sneaker['gender'] . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td><strong>Colorway:</strong> ' . $sneaker['colorway'] . '</td>';
        echo '<td><strong>Release Date:</strong> ' . $sneaker['releasedate'] . '</td>';
        echo '</tr>';
        echo '</table>';

        echo '<div class="sizes-header">';
        echo '<h3>Available Sizes</h3>';
        echo '<div class="size-actions">';

        echo '<form method="post" action="updateprocess.php">';
        echo '<input type="hidden" id="sneakeridInput" name="sneakerid" value="' . htmlspecialchars($sneakerid) . '">';
        echo '<button type="submit">EDIT</button>';
        echo '</form>';
        
        echo '<form method="post" action="">';
        echo '<input type="hidden" name="sneakerid" value="' . $sneakerid . '">';

        echo '<button type="submit" name="delete" onclick="showConfirmationPopup(); return false;"';
        echo ' style="background-color: #ff0000; color: white; border: none; padding: 8px 16px; cursor: pointer;">DELETE</button>';
        
        echo '</form>';
        echo '</div>';
        echo '</div>';

        echo '<div class="table-container">';
        echo '<div class="table-scroll">';
        echo '<table class="responsive-table2">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Size</th>';
        echo '<th>Quantity</th>';
        echo '<th>Price</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        $detailsQuery = "SELECT s.sizename, i.quantity, p.price 
                        FROM sizes s
                        LEFT JOIN inventory i ON s.sizeid = i.sizeid AND i.sneakerid = '$sneakerid'
                        LEFT JOIN prices p ON s.sizeid = p.sizeid AND p.sneakerid = '$sneakerid'";

        $detailsResult = $mysqli->query($detailsQuery);

        while ($row = $detailsResult->fetch_assoc()) {
            echo '<tr>';
            echo '<td style="text-align: center;"> EU ' . $row['sizename'] . '</td>';
            echo '<td style="text-align: center;">' . ($row['quantity'] ?? 'N/A') . '</td>';
            echo '<td style="text-align: center;">$' . ($row['price'] ?? 'N/A') . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';

        $detailsResult->free();
    } else {
        echo '<p>No details found for this sneaker ID.</p>';
    }
    $result->free();
} else {
    echo '<p>Invalid sneaker ID.</p>';
}
$mysqli->close();
?>