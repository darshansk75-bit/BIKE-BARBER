<?php
require_once 'c:/wamp64/www/PROJECT_UPDATED/config/db.php';
$sql = "CREATE TABLE IF NOT EXISTS manufacturer_recordings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    manufacturer_id INT NOT NULL,
    product_details TEXT NOT NULL,
    order_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(manufacturer_id) ON DELETE CASCADE
) ENGINE=InnoDB;";
if (mysqli_query($conn, $sql)) {
    echo "Table 'manufacturer_recordings' created successfully.";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}
?>
