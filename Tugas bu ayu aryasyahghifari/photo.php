<?php
require_once 'functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('index.php');
}

 $photo_id = (int)$_GET['id']; 
 $photo = getPhotoById($conn, $photo_id);

if (!$photo) {
    $_SESSION['error_message'] = 'Foto tidak ditemukan.';
    redirect('index.php');
}

 $is_logged_in = isLoggedIn();
 $current_user_id = getCurrentUserId();
 $is_photo_owner = ($is_logged_in && $current_user_id == $photo['user_id']);

 $comments = getPhotoComments($conn, $photo_id);
 $like_count = getLikeCount($conn, $photo_id);
 $is_liked = ($is_logged_in) ? isPhotoLiked($conn, $current_user_id, $photo_id) : false;

 $user_collections = [];
 $saved_collection_ids = [];
if ($is_logged_in) {
    $user_collections = getUserCollections($conn, $current_user_id);
    $saved_collection_ids = getSavedCollectionsForPhoto($conn, $current_user_id, $photo_id);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'toggle_like') {
        if ($is_liked) {
            $sql = "DELETE FROM likes WHERE user_id = ? AND photo_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $current_user_id, $photo_id);
            $stmt->execute();
        } else {
            $sql = "INSERT INTO likes (user_id, photo_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $current_user_id, $photo_id);
            $stmt->execute();
        }
        header("Location: photo.php?id=" . $photo_id); 
        exit();
    }

    if (isset($_POST['comment']) && !empty(trim($_POST['comment']))) {
        $comment_text = trim($_POST['comment']);
        $sql = "INSERT INTO comments (user_id, photo_id, comment) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $current_user_id, $photo_id, $comment_text);
        $stmt->execute();
        header("Location: photo.php?id=" . $photo_id);
        exit();
    }

    if (isset($_POST['action']) && $_POST['action'] == 'save_to_collection' && isset($_POST['collection_id'])) {
        $collection_id = (int)$_POST['collection_id'];
        
        if (in_array($collection_id, $saved_collection_ids)) {
            $sql = "DELETE FROM saved_photos WHERE user_id = ? AND photo_id = ? AND collection_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $current_user_id, $photo_id, $collection_id);
            $stmt->execute();
        } else {
            $sql = "INSERT INTO saved_photos (user_id, photo_id, collection_id) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $current_user_id, $photo_id, $collection_id);
            $stmt->execute();
        }
        header("Location: photo.php?id=" . $photo_id);
        exit();
    }
}

require_once 'header.php';
?>

<div class="photo-detail-container">
    <div class="photo-detail-image">
        <img src="uploads/<?php echo htmlspecialchars($photo['file_name']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>">
    </div>
    <div class="photo-detail-info">
        <div class="photo-title-and-actions">
            <h2><?php echo htmlspecialchars($photo['title']); ?></h2>
            <!-- TOMBOL EDIT & HAPUS: HANYA UNTUK PEMILIK FOTO -->
            <?php if ($is_photo_owner): ?>
                <div class="photo-owner-actions">
                    <a href="edit_photo.php?id=<?php echo $photo_id; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                    <a href="delete_photo.php?id=<?php echo $photo_id; ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus foto ini?');"><i class="fas fa-trash"></i> Hapus</a>
                </div>
            <?php endif; ?>
        </div>
        
        <p>Diunggah oleh <strong><?php echo htmlspecialchars($photo['username']); ?></strong> pada <?php echo formatTime($photo['created_at']); ?></p>
        
        <div class="photo-actions-detail">
            <!-- FITUR LIKE: UNTUK SEMUA USER YANG SUDAH LOGIN -->
            <?php if ($is_logged_in): ?>
                <form action="photo.php?id=<?php echo $photo_id; ?>" method="post" class="like-form">
                    <input type="hidden" name="action" value="toggle_like">
                    <button type="submit" class="btn-like <?php echo $is_liked ? 'liked' : ''; ?>">
                        <i class="fas fa-heart"></i> <?php echo $is_liked ? 'Batal Suka' : 'Suka'; ?>
                    </button>
                </form>
            <?php endif; ?>
            <span class="like-count-detail"><i class="fas fa-heart"></i> <?php echo $like_count; ?> Suka</span>
        </div>

        <!-- FITUR SIMPAN KE KOLEKSI: UNTUK SEMUA USER YANG SUDAH LOGIN -->
        <?php if ($is_logged_in): ?>
            <?php if (!empty($user_collections)): ?>
                <div class="save-to-collection-section">
                    <button class="btn-save-toggle" onclick="toggleCollectionList()">
                        <i class="fas fa-bookmark"></i> Simpan
                    </button>
                    <div id="collection-list" class="collection-list" style="display: none;">
                        <p>Pilih koleksi:</p>
                        <?php foreach ($user_collections as $collection): ?>
                            <form action="photo.php?id=<?php echo $photo_id; ?>" method="post" class="save-collection-form">
                                <input type="hidden" name="action" value="save_to_collection">
                                <input type="hidden" name="collection_id" value="<?php echo $collection['id']; ?>">
                                <button type="submit" class="collection-item <?php echo in_array($collection['id'], $saved_collection_ids) ? 'saved' : ''; ?>">
                                    <i class="fas fa-<?php echo in_array($collection['id'], $saved_collection_ids) ? 'check-square' : 'square'; ?>"></i>
                                    <?php echo htmlspecialchars($collection['name']); ?>
                                </button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <p><a href="collections.php">Buat koleksi terlebih dahulu untuk menyimpan foto.</a></p>
            <?php endif; ?>
        <?php endif; ?>

        <hr>

        <div class="comments-section">
            <h3>Komentar</h3>
            <?php if ($is_logged_in): ?>
                <form action="photo.php?id=<?php echo $photo_id; ?>" method="post" class="comment-form">
                    <textarea name="comment" placeholder="Tulis komentar..." required></textarea>
                    <button type="submit" class="btn">Kirim Komentar</button>
                </form>
            <?php endif; ?>
            
            <?php if (empty($comments)): ?>
                <p>Belum ada komentar.</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                        <small><?php echo formatTime($comment['created_at']); ?></small>
                        <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>