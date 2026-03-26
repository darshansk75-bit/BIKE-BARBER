<?php
/**
 * Database Toolbox — Interactive Maintenance Console
 * Consolidates all diagnostic, seeding, and schema checking scripts.
 * 
 * Usage: 
 * ?action=list_tables           Shows all tables in database
 * ?action=describe&table=...    Shows detailed schema for a table
 * ?action=seed_wallet           Inserts default wallet row if missing
 * ?action=check_health          Quick check for critical table existence
 */

header('Content-Type: text/plain');

// Adjust path back to config/db.php from /scripts/database/
require_once __DIR__ . '/../../config/db.php';

$action = $_GET['action'] ?? 'list_tables';
$table = $_GET['table'] ?? '';

echo "--- BIKE BARBER DATABASE TOOLBOX ---\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "------------------------------------\n\n";

switch ($action) {
    case 'list_tables':
        $res = mysqli_query($conn, "SHOW TABLES");
        if (!$res) { echo "ERROR listing tables: " . mysqli_error($conn) . "\n"; exit; }
        
        echo "DATABASE TABLES:\n";
        while ($row = mysqli_fetch_row($res)) {
            echo " - " . str_pad($row[0], 25) . " (Use ?action=describe&table=" . $row[0] . " to view schema)\n";
        }
        break;

    case 'describe':
        if (empty($table)) {
            echo "ERROR: Please specify a table name in the ?table= parameter.\n";
            echo "Example: ?action=describe&table=customers\n";
            break;
        }
        
        $table_esc = mysqli_real_escape_string($conn, $table);
        $res = mysqli_query($conn, "DESCRIBE $table_esc");
        
        if ($res) {
            echo "SCHEMA FOR TABLE '" . strtoupper($table) . "':\n";
            echo str_pad("Field", 25) . " | " . str_pad("Type", 20) . " | Null | Key | Default\n";
            echo str_repeat("-", 80) . "\n";
            while ($row = mysqli_fetch_assoc($res)) {
                echo str_pad($row['Field'], 25) . " | " . 
                     str_pad($row['Type'], 20) . " | " . 
                     str_pad($row['Null'], 4) . " | " . 
                     str_pad($row['Key'], 3) . " | " . 
                     $row['Default'] . "\n";
            }
        } else {
            echo "ERROR describing table '" . $table . "': " . mysqli_error($conn) . "\n";
        }
        break;

    case 'seed_wallet':
        $check = mysqli_query($conn, "SELECT COUNT(*) FROM admin_wallet");
        if (!$check) {
            echo "ERROR: Table 'admin_wallet' does not exist or SQL error: " . mysqli_error($conn) . "\n";
            break;
        }
        $count = mysqli_fetch_row($check)[0];
        if ($count == 0) {
            mysqli_query($conn, "INSERT INTO admin_wallet (admin_id, total_investment, total_sales, total_expense, total_profit, bank_balance) VALUES (1, 0, 0, 0, 0, 0)");
            echo "SUCCESS: Default wallet row inserted.\n";
        } else {
            echo "INFO: Wallet row already exists ($count rows found).\n";
        }
        break;

    case 'check_health':
        $critical_tables = ['admin_wallet', 'customers', 'service_bookings', 'products', 'orders', 'admins', 'categories'];
        echo "CHECKING TABLE HEALTH:\n";
        echo str_pad("Table Name", 20) . " | Status\n";
        echo str_repeat("-", 40) . "\n";
        foreach ($critical_tables as $t) {
            $check = mysqli_query($conn, "SELECT 1 FROM $t LIMIT 1");
            echo str_pad($t, 20) . " | " . ($check ? "OK (Exists)" : "!!! FAILED (" . mysqli_error($conn) . ")") . "\n";
        }
        break;

    default:
        echo "ERROR: Unknown action '" . htmlspecialchars($action) . "'.\n";
        echo "Supported actions: list_tables, describe, seed_wallet, check_health.\n";
}

echo "\n------------------------------------\n";
echo "End of Output.\n";
