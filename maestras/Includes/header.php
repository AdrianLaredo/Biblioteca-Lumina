<head>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<!-- Encabezado mejorado -->
<header class="main-header">
    <div class="header-content">
        <div class="header-left">
            <div class="logo-container">
                <i class="fas fa-book-open fa-2x logo-icon"></i>
                <div class="logo-text">
                    <h1 class="header-title">Biblioteca Lumina</h1>
                    <p class="header-subtitle">Sistema de Gestión Integral</p>
                </div>
            </div>
        </div>
        <div class="header-right">
            <?php if (isset($_SESSION['usr'])): ?>
                <div class="user-info">
                    <div class="welcome-message">
                        Bienvenido, <span class="username"><?php echo htmlspecialchars($_SESSION['usr']); ?></span>
                    </div>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['usr'], 0, 1)); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

