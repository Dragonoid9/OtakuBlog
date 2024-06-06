<?php
session_start();
require_once '../../functions/helpers.php';
require_once '../../functions/pdo_connection.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    redirect('auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply_id = $_POST['reply_id'];

    // Get the comment_id to redirect back to the detail page
    $query = "SELECT comment_id FROM replies WHERE id = ?";
    $statement = $pdo->prepare($query);
    $statement->execute([$reply_id]);
    $reply = $statement->fetch();

    // Get the post_id from the comment
    $query = "SELECT post_id FROM comments WHERE id = ?";
    $statement = $pdo->prepare($query);
    $statement->execute([$reply->comment_id]);
    $comment = $statement->fetch();

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Delete the reply
        $query = "DELETE FROM replies WHERE id = ?";
        $statement = $pdo->prepare($query);
        $statement->execute([$reply_id]);

        // Commit transaction
        $pdo->commit();

        // Redirect back to the detail page
        redirect('detail.php?post_id=' . $comment->post_id);
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }
}
?>