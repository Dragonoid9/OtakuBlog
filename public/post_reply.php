<?php
session_start();
require_once '../functions/helpers.php';
require_once '../functions/pdo_connection.php';

if (!isset($_SESSION['user'])) {
    redirect(url('auth/login.php'));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = $_POST['comment_id'];
    $user_id = $_SESSION['user_id'];
    $reply = $_POST['reply'];

    $query = "INSERT INTO replies (comment_id, user_id, reply, approved, created_at) VALUES (?, ?, ?, FALSE, NOW())";
    $statement = $pdo->prepare($query);
    $statement->execute([$comment_id, $user_id, $reply]);

    // Get the post ID to redirect back to the detail page
    $query = "SELECT post_id FROM comments WHERE id = ?";
    $statement = $pdo->prepare($query);
    $statement->execute([$comment_id]);
    $comment = $statement->fetch();

    redirect('detail.php?post_id=' . $comment->post_id);
}
?>
