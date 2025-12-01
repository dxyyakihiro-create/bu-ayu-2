<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

 $error = '';
 $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $file = $_FILES['photo'];

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; 

    if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
        
        $file_name = time() . '-' . basename($file['name']);
        $target_file = 'uploads/' . $file_name;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            
            $user_id = getCurrentUserId();
            $sql = "INSERT INTO photos (user_id, title, file_name) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $user_id, $title, $file_name);
            
            if ($stmt->execute()) {
                $success = 'Foto berhasil diupload!';
            } else {
                $error = 'Gagal menyimpan info foto ke database.';
            }
        } else {
            $error = 'Gagal mengupload file.';
        }
    } else {
        $error = 'File tidak valid. Harap upload gambar (JPG, PNG, GIF) dengan ukuran maksimal 5MB.';
    }
}
?>

<?php require_once 'header.php'; ?>

<div class="form-container">
    <h2>Upload Foto Baru</h2>
    <?php if ($error): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Judul Foto</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="photo">Pilih Foto</label>
            <input type="file" id="photo" name="photo" accept="image/*" required>
        </div>
        <button type="submit" class="btn">Upload</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>