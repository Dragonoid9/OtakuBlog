<?php
session_start();
require_once '../functions/helpers.php';
require_once '../functions/pdo_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'];
    $rating = $_POST['rating'];
    $user_id = $_SESSION['user_id'];

    // Check if user ID is set
    if (!isset($user_id)) {
        die('User not logged in.');
    }

    // Check if the user has already rated this post
    $query = "SELECT id FROM ratings WHERE post_id = ? AND user_id = ?";
    $statement = $pdo->prepare($query);
    $statement->execute([$post_id, $user_id]);
    $existingRatingId = $statement->fetchColumn();

    try {
        $pdo->beginTransaction();
        if ($existingRatingId) {
            // Update the existing rating
            $query = "UPDATE ratings SET rating = ? WHERE id = ?";
            $statement = $pdo->prepare($query);
            $statement->execute([$rating, $existingRatingId]);
            echo "Rating updated successfully.";
        } else {
            // Insert a new rating
            $query = "INSERT INTO ratings (post_id, user_id, rating) VALUES (?, ?, ?)";
            $statement = $pdo->prepare($query);
            $statement->execute([$post_id, $user_id, $rating]);
            echo "Rating submitted successfully.";
        }
        $pdo->commit();
        redirect('detail.php?post_id=' . $post_id);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>
