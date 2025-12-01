<?php
require_once 'functions.php';

if (!isAdmin()) {
    redirect('index.php');
}

 $sql = "SELECT p.id, p.title, p.file_name, p.created_at, u.username 
        FROM photos p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC";
 $result = $conn->query($sql);
 $photos = $result->fetch_all(MYSQLI_ASSOC);
?>

<?php require_once 'header.php'; ?>

<div class="admin-dashboard">
    <h1>Dashboard Admin</h1>
    
    <div class="admin-section">
        <h2>Kelola Semua Foto</h2>
        <div class="admin-photo-grid">
            <?php if (empty($photos)): ?>
                <p>Belum ada foto di situs.</p>
            <?php else: ?>
                <?php foreach ($photos as $photo): ?>
                    <div class="admin-photo-card">
                        <img src="uploads/<?php echo htmlspecialchars($photo['file_name']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                        <div class="admin-photo-info">
                            <h4><?php echo htmlspecialchars($photo['title']); ?></h4>
                            <p>oleh: <?php echo htmlspecialchars($photo['username']); ?></p>
                            <small><?php echo formatTime($photo['created_at']); ?></small>
                            <div class="admin-photo-actions">
                                <a href="photo.php?id=<?php echo $photo['id']; ?>" class="btn-view"><i class="fas fa-eye"></i> Lihat</a>
                                <a href="edit_photo.php?id=<?php echo $photo['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                <a href="delete_photo.php?id=<?php echo $photo['id']; ?>" class="btn-delete" onclick="return confirm('Hapus foto ini?');"><i class="fas fa-trash"></i> Hapus</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>