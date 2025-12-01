<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

 $user_id = getCurrentUserId();
 $user = getUserById($conn, $user_id);

 $error = '';
 $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {

    if (updateProfileImage($conn, $user_id, $_FILES['profile_image'])) {
        $success = 'Foto profil berhasil diperbarui!';
    
        $user = getUserById($conn, $user_id);
    } else {
        $error = 'Gagal mengupload foto. Pastikan file adalah gambar (JPG, PNG, GIF) dan ukurannya maksimal 2MB.';
    }
}
?>

<?php require_once 'header.php'; ?>

<div class="form-container">
    <h2>Edit Profil</h2>
    
    <div class="profile-picture-preview">
        <img src="<?php echo getProfileImagePath($user); ?>" alt="Profile Picture">
    </div>

    <?php if ($error): ?>
        <div class="alert error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="edit_profile.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="profile_image">Upload Foto Profil Baru</label>
            <input type="file" id="profile_image" name="profile_image" accept="image/*">
        </div>
        <button type="submit" class="btn">Simpan Perubahan</button>
    </form>
    <a href="profile.php?id=<?php echo $user_id; ?>" class="btn-cancel">Kembali ke Profil</a>
</div>

<?php require_once 'footer.php'; ?>