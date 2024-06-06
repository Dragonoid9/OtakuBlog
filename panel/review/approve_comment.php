<?php
session_start();
require_once '../../functions/helpers.php';
require_once '../../functions/pdo_connection.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    redirect('auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = $_POST['comment_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $query = "UPDATE comments SET approved = TRUE WHERE id = ?";
    } else {
        $query = "DELETE FROM comments WHERE id = ?";
    }
    
    $statement = $pdo->prepare($query);
    $statement->execute([$comment_id]);
}

redirect('panel/review');