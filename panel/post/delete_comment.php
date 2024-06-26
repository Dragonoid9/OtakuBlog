<?php
session_start();
require_once '../../functions/helpers.php';
require_once '../../functions/pdo_connection.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    redirect('auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = $_POST['comment_id'];

    // Get the post_id to redirect back to the detail page
    $query = "SELECT post_id FROM comments WHERE id = ?";
    $statement = $pdo->prepare($query);
    $statement->execute([$comment_id]);
    $comment = $statement->fetch();

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Delete replies associated with the comment
        $query = "DELETE FROM replies WHERE comment_id = ?";
        $statement = $pdo->prepare($query);
        $statement->execute([$comment_id]);

        // Delete the comment
        $query = "DELETE FROM comments WHERE id = ?";
        $statement = $pdo->prepare($query);
        $statement->execute([$comment_id]);

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
