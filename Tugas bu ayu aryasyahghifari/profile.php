<?php
require_once 'functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('index.php');
}

 $user_id = (int)$_GET['id'];
 $user = getUserById($conn, $user_id);

if (!$user) {
    $_SESSION['error_message'] = 'Pengguna tidak ditemukan.';
    redirect('index.php');
}

 $is_own_profile = isLoggedIn() && getCurrentUserId() == $user_id;

 $sql_uploaded = "SELECT * FROM photos WHERE user_id = ? ORDER BY created_at DESC";
 $stmt_uploaded = $conn->prepare($sql_uploaded);
 $stmt_uploaded->bind_param("i", $user_id);
 $stmt_uploaded->execute();
 $uploaded_photos = $stmt_uploaded->get_result()->fetch_all(MYSQLI_ASSOC);

 $liked_photos = [];
if ($is_own_profile) {
    $liked_photos = getLikedPhotosByUser($conn, $user_id);
}
?>

<?php require_once 'header.php'; ?>

<div class="profile-header">
    <img src="<?php echo getProfileImagePath($user); ?>" alt="Profile Picture" class="profile-avatar">
    <h1><?php echo htmlspecialchars($user['username']); ?></h1>
    <p>Bergabung pada <?php echo formatTime($user['created_at']); ?></p>
    
    <?php if ($is_own_profile): ?>
        <a href="edit_profile.php" class="btn-edit-profile"><i class="fas fa-camera"></i> Edit Foto Profil</a>
    <?php endif; ?>
</div>

<div class="profile-tabs">
    <button class="tab-button active" onclick="showTab('my-photos')">Foto Saya (<?php echo count($uploaded_photos); ?>)</button>
    <?php if ($is_own_profile): ?>
        <button class="tab-button" onclick="showTab('liked-photos')">Foto Disukai (<?php echo count($liked_photos); ?>)</button>
    <?php endif; ?>
</div>

<div id="my-photos" class="tab-content">
    <div class="photo-grid">
        <?php if (empty($uploaded_photos)): ?>
            <p>Belum ada foto.</p>
        <?php else: ?>
            <?php foreach ($uploaded_photos as $photo): ?>
                <div class="photo-card">
                    <div class="photo-card-container">
                        <a href="photo.php?id=<?php echo $photo['id']; ?>">
                            <img src="uploads/<?php echo htmlspecialchars($photo['file_name']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                        </a>
                        <?php if ($is_own_profile): ?>
                            <div class="photo-card-overlay">
                                <div class="photo-card-actions">
                                    <a href="edit_photo.php?id=<?php echo $photo['id']; ?>" class="btn-edit-small" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="delete_photo.php?id=<?php echo $photo['id']; ?>" class="btn-delete-small" title="Hapus" onclick="return confirm('Hapus foto ini?');"><i class="fas fa-trash"></i></a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="photo-info">
                        <h3><?php echo htmlspecialchars($photo['title']); ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php if ($is_own_profile): ?>
<div id="liked-photos" class="tab-content" style="display:none;">
    <div class="photo-grid">
        <?php if (empty($liked_photos)): ?>
            <p>Anda belum menyukai foto apa pun.</p>
        <?php else: ?>
            <?php foreach ($liked_photos as $photo): ?>
                <div class="photo-card">
                    <a href="photo.php?id=<?php echo $photo['id']; ?>">
                        <img src="uploads/<?php echo htmlspecialchars($photo['file_name']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                    </a>
                    <div class="photo-info">
                        <h3><?php echo htmlspecialchars($photo['title']); ?></h3>
                        <p>oleh <a href="profile.php?id=<?php echo $photo['user_id']; ?>"><?php echo htmlspecialchars($photo['username']); ?></a></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>