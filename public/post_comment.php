<?php
session_start();
require_once '../functions/helpers.php';
require_once '../functions/pdo_connection.php';

if (!isset($_SESSION['user'])) {
    redirect(url('auth/login.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];
    $comment = $_POST['comment'];

    $query = "INSERT INTO comments (user_id, post_id, comment, approved, created_at) VALUES (?, ?, ?, FALSE, NOW())";
    $statement = $pdo->prepare($query);
    $statement->execute([$user_id, $post_id, $comment]);

    // Redirect back to the detail page
    redirect('detail.php?post_id=' . $post_id);
}
?>
