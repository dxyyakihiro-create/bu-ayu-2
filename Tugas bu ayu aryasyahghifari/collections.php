<?php
require_once 'functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

 $user_id = getCurrentUserId();
 $collections = getUserCollections($conn, $user_id);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['collection_name'])) {
    $name = $_POST['collection_name'];
    $sql = "INSERT INTO collections (user_id, name) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $name);
    $stmt->execute();
    header("Refresh:0"); 
}
?>

<?php require_once 'header.php'; ?>

<div class="collections-header">
    <h1>Koleksi Saya</h1>
    <form action="collections.php" method="post" class="add-collection-form">
        <input type="text" name="collection_name" placeholder="Nama koleksi baru" required>
        <button type="submit" class="btn">Tambah Koleksi</button>
    </form>
</div>

<div class="collections-list">
    <?php if (empty($collections)): ?>
        <p>eksi peAnda belum memiliki koleksi. Buat kolrtama Anda!</p>
    <?php else: ?>
        <?php foreach ($collections as $collection): ?>
            <?php 
                // Ambil foto untuk setiap koleksi
                $collection_photos = getCollectionPhotos($conn, $collection['id']);
            ?>
            <div class="collection-card">
                <h3><?php echo htmlspecialchars($collection['name']); ?></h3>
                <p><?php echo count($collection_photos); ?> foto</p>
                <p>Dibuat pada <?php echo formatTime($collection['created_at']); ?></p>
                
                <div class="collection-photos-grid">
                    <?php if (empty($collection_photos)): ?>
                        <p>Koleksi ini kosong.</p>
                    <?php else: ?>
                        <?php foreach ($collection_photos as $photo): ?>
                            <div class="mini-photo-card">
                                <a href="photo.php?id=<?php echo $photo['id']; ?>">
                                    <img src="uploads/<?php echo htmlspecialchars($photo['file_name']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>