<?php
/**
 * Admin Header Include
 * Provides: Bootstrap 5, Google Fonts, Chart.js, admin CSS
 * PATH: /includes/admin_header.php
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>
      const savedTheme = localStorage.getItem('bikebarber_theme') || 'dark';
      if (savedTheme === 'system') {
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
      } else {
        document.documentElement.setAttribute('data-theme', savedTheme);
      }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo SITE_NAME; ?> — Admin Dashboard">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' — ' : ''; ?>Admin | BIKE BARBER</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Round" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Chart.js (ready for Task 2) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <!-- Admin Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css?v=<?= time() ?>">
</head>
<body class="sidebar-collapsed">
