<?php
/**
 * Export Controller
 * PATH: /admin/export.php
 * Handles CSV, Excel, and PDF exports for various modules.
 */

// Enable error reporting for debugging during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/session_auth.php';
auth_guard('admin'); // Security guard
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

// Parameters
$module = $_GET['module'] ?? '';
$type = $_GET['type'] ?? 'csv';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

if (!$module) {
    die("Error: Module not specified.");
}

// 1. Data Fetching Logic based on Module
$data = [];
$headers = [];
$filename = $module . "_report_" . date('Ymd');

switch ($module) {
    case 'orders':
        $headers = ['Order ID', 'Customer Name', 'Total Amount', 'Payment Status', 'Order Status', 'Date'];
        $query = "SELECT o.order_id, c.name as customer_name, o.payment_status, o.order_status, o.order_date, 
                  (SELECT SUM(price * quantity) FROM order_items WHERE order_id = o.order_id) as total_amount
                  FROM orders o 
                  JOIN customers c ON o.customer_id = c.customer_id 
                  WHERE 1=1";
        
        if ($from_date) $query .= " AND DATE(o.order_date) >= '" . mysqli_real_escape_string($conn, $from_date) . "'";
        if ($to_date) $query .= " AND DATE(o.order_date) <= '" . mysqli_real_escape_string($conn, $to_date) . "'";
        if ($status && $status != 'All') $query .= " AND o.order_status = '" . mysqli_real_escape_string($conn, $status) . "'";
        if ($search) {
            $s = mysqli_real_escape_string($conn, $search);
            $query .= " AND (c.name LIKE '%$s%' OR o.order_id LIKE '%$s%')";
        }
        $query .= " ORDER BY o.order_date DESC";
        $res = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($res)) {
            $data[] = [
                $row['order_id'],
                $row['customer_name'],
                '₹' . number_format($row['total_amount'] ?? 0, 2),
                $row['payment_status'],
                $row['order_status'],
                date('d M Y', strtotime($row['order_date']))
            ];
        }
        break;

    case 'wallet':
        $headers = ['Transaction ID', 'Description', 'Amount', 'Type', 'Date'];
        $query = "SELECT * FROM wallet_transactions WHERE 1=1";
        
        if ($from_date) $query .= " AND DATE(created_at) >= '" . mysqli_real_escape_string($conn, $from_date) . "'";
        if ($to_date) $query .= " AND DATE(created_at) <= '" . mysqli_real_escape_string($conn, $to_date) . "'";
        if ($status && $status != 'All') $query .= " AND type = '" . mysqli_real_escape_string($conn, $status) . "'";
        if ($search) {
            $s = mysqli_real_escape_string($conn, $search);
            $query .= " AND (description LIKE '%$s%' OR transaction_id LIKE '%$s%')";
        }
        $query .= " ORDER BY created_at DESC";
        $res = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($res)) {
            $data[] = [
                'TXN' . str_pad($row['transaction_id'], 4, '0', STR_PAD_LEFT),
                $row['description'],
                '₹' . number_format($row['amount'], 2),
                $row['type'],
                date('d M Y', strtotime($row['created_at']))
            ];
        }
        break;

    case 'analytics':
        $headers = ['Metric', 'Value'];
        // Generic Analytics summary for PDF/Excel
        $total_orders = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM orders"))[0];
        $total_revenue = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(amount) FROM wallet_transactions WHERE type='SALE'"))[0] ?: 0;
        $total_customers = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM customers"))[0];
        $total_txns = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM wallet_transactions"))[0];
        
        $data = [
            ['Total Orders', $total_orders],
            ['Total Revenue', '₹' . number_format($total_revenue, 2)],
            ['Total Customers', $total_customers],
            ['Total Transactions', $total_txns],
            ['Report Generated', date('d M Y H:i')]
        ];
        break;

    default:
        die("Error: Invalid module.");
}

// 2. Export Output Generation
if ($type == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, $headers);
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;

} elseif ($type == 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set Headers
    $col = 'A';
    foreach ($headers as $h) {
        $sheet->setCellValue($col . '1', $h);
        $sheet->getStyle($col . '1')->getFont()->setBold(true);
        $sheet->getColumnDimension($col)->setAutoSize(true);
        $col++;
    }
    
    // Set Data
    $rowIdx = 2;
    foreach ($data as $row) {
        $col = 'A';
        foreach ($row as $val) {
            $sheet->setCellValue($col . $rowIdx, $val);
            $col++;
        }
        $rowIdx++;
    }
    
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;

} elseif ($type == 'pdf') {
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    
    $html = '
    <html>
    <head>
        <style>
            body { font-family: "Helvetica", sans-serif; color: #333; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
            .title { font-size: 24px; font-weight: bold; text-transform: uppercase; color: #1a1a1a; }
            .meta { font-size: 12px; color: #666; margin-top: 5px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th { background-color: #f8fafc; color: #475569; text-align: left; padding: 12px; border: 1px solid #e2e8f0; font-size: 12px; text-transform: uppercase; }
            td { padding: 10px; border: 1px solid #e2e8f0; font-size: 11px; }
            .footer { margin-top: 30px; font-size: 10px; text-align: center; color: #94a3b8; }
            .total-row { background-color: #f1f5f9; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="title">' . strtoupper($module) . ' REPORT</div>
            <div class="meta">Generated on: ' . date('d M Y, h:i A') . '</div>
        </div>
        <table>
            <thead><tr>';
    foreach ($headers as $h) {
        $html .= '<th>' . $h . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $val) {
            $html .= '<td>' . htmlspecialchars($val) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table>
        <div class="footer">
            © ' . date('Y') . ' BikeBarber Admin System. All rights reserved.
        </div>
    </body>
    </html>';
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream($filename . ".pdf", ["Attachment" => true]);
    exit;
}
?>
