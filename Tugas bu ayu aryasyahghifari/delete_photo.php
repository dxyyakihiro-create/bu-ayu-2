<?php
require_once 'functions.php';

// Pastikan user sudah login
if (!isLoggedIn()) {
    redirect('login.php');
}

// Pastikan ada ID foto
if (!isset($_GET['id'])) {
    redirect('index.php');
}

 $photo_id = $_GET['id'];
 $photo = getPhotoById($conn, $photo_id);

if (!$photo) {
    $_SESSION['error_message'] = 'Foto tidak ditemukan.';
    redirect('index.php');
}

 $current_user_id = getCurrentUserId();
if ($current_user_id != $photo['user_id'] && !isAdmin()) {
    $_SESSION['error_message'] = 'Anda tidak memiliki izin untuk menghapus foto ini.';
    redirect('photo.php?id=' . $photo_id);
}

if (deletePhotoById($conn, $photo_id)) {
    $_SESSION['success_message'] = 'Foto berhasil dihapus.';
} else {
    $_SESSION['error_message'] = 'Gagal menghapus foto.';
}

 $redirect_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
redirect($redirect_to);
?>