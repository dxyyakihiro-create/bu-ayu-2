<?php
require_once 'database.php';

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? 0;
}

function getUserById($conn, $userId) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getPhotoById($conn, $photoId) {
    $sql = "SELECT p.*, u.username FROM photos p JOIN users u ON p.user_id = u.id WHERE p.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $photoId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function isPhotoLiked($conn, $userId, $photoId) {
    $sql = "SELECT * FROM likes WHERE user_id = ? AND photo_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $photoId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getLikeCount($conn, $photoId) {
    $sql = "SELECT COUNT(*) as count FROM likes WHERE photo_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $photoId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'];
}

function getPhotoComments($conn, $photoId) {
    $sql = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.photo_id = ? ORDER BY c.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $photoId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getUserCollections($conn, $userId) {
    $sql = "SELECT * FROM collections WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getCollectionPhotos($conn, $collectionId) {
    $sql = "SELECT p.*, u.username FROM photos p 
            JOIN saved_photos sp ON p.id = sp.photo_id 
            JOIN users u ON p.user_id = u.id 
            WHERE sp.collection_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $collectionId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function formatTime($timestamp) {
    $date = new DateTime($timestamp);
    return $date->format('d M Y');
}

function getLikedPhotosByUser($conn, $userId) {

    $sql = "SELECT p.*, u.username FROM photos p 
            JOIN likes l ON p.id = l.photo_id 
            JOIN users u ON p.user_id = u.id 
            WHERE l.user_id = ? 
            ORDER BY l.id DESC"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function deletePhotoById($conn, $photoId) {

    $sql = "SELECT file_name FROM photos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $photoId);
    $stmt->execute();
    $photo = $stmt->get_result()->fetch_assoc();

    if ($photo) {
        $file_path = 'uploads/' . $photo['file_name'];
        
        $conn->query("DELETE FROM saved_photos WHERE photo_id = $photoId");
        $conn->query("DELETE FROM comments WHERE photo_id = $photoId");
        $conn->query("DELETE FROM likes WHERE photo_id = $photoId");
        
        $conn->query("DELETE FROM photos WHERE id = $photoId");

        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        return true;
    }
    return false;
}

function getSavedCollectionsForPhoto($conn, $userId, $photoId) {
    $sql = "SELECT c.id, c.name FROM collections c
            JOIN saved_photos sp ON c.id = sp.collection_id
            WHERE sp.user_id = ? AND sp.photo_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $photoId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $savedCollectionIds = [];
    foreach($result as $row) {
        $savedCollectionIds[] = $row['id'];
    }
    return $savedCollectionIds;
}
function getProfileImagePath($user) {
    if (!empty($user['profile_image'])) {
        return 'uploads/' . htmlspecialchars($user['profile_image']);
    }
    return 'uploads/default-profile.png';
}

function updateProfileImage($conn, $userId, $file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; 

    if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = 'profile_' . $userId . '_' . time() . '.' . $file_extension;
        $target_file = 'uploads/' . $file_name;
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            
            $sql = "SELECT profile_image FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result && !empty($result['profile_image']) && file_exists('uploads/' . $result['profile_image'])) {
                unlink('uploads/' . $result['profile_image']); 
            }
            $sql = "UPDATE users SET profile_image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $file_name, $userId);
            
            return $stmt->execute();
        }
    }
    return false;
}

?>