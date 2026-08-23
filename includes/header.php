<?php
// Determina la pagina corrente per la classe 'active'
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Acquisti Dashboard</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- CSS del progetto -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <header class="app-header">
        <div class="container header-inner">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-chart-line"></i> Gestione Acquisti
            </a>
            <nav class="nav-links">
                <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-house"></i> Dashboard
                </a>
                <a href="search.php" class="nav-link <?php echo ($current_page == 'search.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-magnifying-glass"></i> Ricerca Avanzata
                </a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">