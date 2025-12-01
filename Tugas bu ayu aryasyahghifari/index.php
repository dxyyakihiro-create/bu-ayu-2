<?php
require_once 'functions.php';
require_once 'header.php';

 $sql = "SELECT p.*, u.username, 
               (SELECT COUNT(*) FROM likes WHERE photo_id = p.id) as like_count
        FROM photos p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC";
 $result = $conn->query($sql);
 $photos = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="photo-grid">
    <?php if (empty($photos)): ?>
        <p>Belum ada foto. <a href="upload.php">Upload foto pertama!</a></p>
    <?php else: ?>
        <?php foreach ($photos as $photo): ?>
            <div class="photo-card">
                <a href="photo.php?id=<?php echo $photo['id']; ?>">
                    <img src="uploads/<?php echo htmlspecialchars($photo['file_name']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                </a>
                <div class="photo-info">
                    <h3><?php echo htmlspecialchars($photo['title']); ?></h3>
                    <p>oleh <a href="profile.php?id=<?php echo $photo['user_id']; ?>"><?php echo htmlspecialchars($photo['username']); ?></a></p>
                    <div class="photo-actions">
                        <span class="like-count"><i class="fas fa-heart"></i> <?php echo $photo['like_count']; ?></span>
                        <span class="comment-count"><i class="fas fa-comment"></i> Komentar</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>