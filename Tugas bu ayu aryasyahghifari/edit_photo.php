<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

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
    $_SESSION['error_message'] = 'Anda tidak memiliki izin untuk mengedit foto ini.';
    redirect('photo.php?id=' . $photo_id);
}

 $error = '';
 $success = '';

// Proses update judul
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_title = $_POST['title'];
    
    if (empty($new_title)) {
        $error = 'Judul foto tidak boleh kosong.';
    } else {
        $sql = "UPDATE photos SET title = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_title, $photo_id);
        
        if ($stmt->execute()) {
            $success = 'Judul foto berhasil diperbarui.';
            // Update data foto yang ditampilkan
            $photo['title'] = $new_title;
        } else {
            $error = 'Gagal memperbarui judul foto.';
        }
    }
}
?>

<?php require_once 'header.php'; ?>

<div class="form-container">
    <h2>Edit Judul Foto</h2>
    
    <div class="photo-preview-edit">
        <img src="uploads/<?php echo htmlspecialchars($photo['file_name']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
    </div>

    <?php if ($error): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="edit_photo.php?id=<?php echo $photo_id; ?>" method="post">
        <div class="form-group">
            <label for="title">Judul Foto</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($photo['title']); ?>" required>
        </div>
        <button type="submit" class="btn">Simpan Perubahan</button>
        <a href="photo.php?id=<?php echo $photo_id; ?>" class="btn-cancel">Batal</a>
    </form>
</div>

<?php require_once 'footer.php'; ?>