<?php
require_once 'database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <a href="index.php" class="logo">
                    <i class="fas fa-camera"></i> Photo Gallery
                </a>
                <div class="nav-links">
                    <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="upload.php"><i class="fas fa-upload"></i> Upload</a>
                        <a href="collections.php"><i class="fas fa-bookmark"></i> Koleksi</a>
                        <a href="profile.php?id=<?php echo getCurrentUserId(); ?>"><i class="fas fa-user"></i> Profil</a>
                        <?php if (isAdmin()): ?>
                            <a href="admin.php"><i class="fas fa-cog"></i> Admin</a>
                        <?php endif; ?>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Masuk</a>
                        <a href="register.php"><i class="fas fa-user-plus"></i> Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    <main class="container">
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert success">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert error">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>