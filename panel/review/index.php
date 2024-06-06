<?php
session_start();
require_once '../../functions/helpers.php';
require_once '../../functions/pdo_connection.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    redirect('auth/login.php');
}

// Fetch pending comments
$query = "SELECT comments.*, users.first_name, users.last_name FROM comments
          JOIN users ON comments.user_id = users.id
          WHERE comments.approved = FALSE";
$statement = $pdo->prepare($query);
$statement->execute();
$pendingComments = $statement->fetchAll();

// Fetch pending replies
$query = "SELECT replies.*, users.first_name, users.last_name FROM replies
          JOIN users ON replies.user_id = users.id
          WHERE replies.approved = FALSE";
$statement = $pdo->prepare($query);
$statement->execute();
$pendingReplies = $statement->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Review Panel</title>
    <link rel="stylesheet" href="<?= asset('assets/css/bootstrap.min.css') ?>" media="all" type="text/css">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>" media="all" type="text/css">
    <link rel="stylesheet" href="<?= asset('assets/css/admin-review.css') ?>" media="all" type="text/css">
</head>
<body>
<section id="app">
    <?php require_once '../layouts/top-nav.php'; ?>

    <section class="container-fluid">
        <section class="row">
            <section class="col-md-2 p-0">
                <?php require_once '../layouts/sidebar.php'; ?>
            </section>
            <section class="col-md-10 pt-3">
                <h1>Admin Review Panel</h1>

                <?php if (!empty($pendingComments)): ?>
                    <h2>Pending Comments</h2>
                    <?php foreach ($pendingComments as $comment): ?>
                        <div class="comment-card p-3 mb-3">
                            <p><strong><?= htmlspecialchars($comment->first_name . ' ' . $comment->last_name) ?></strong> commented:</p>
                            <p><?= htmlspecialchars($comment->comment) ?></p>
                            <form action="<?= url('panel/review/approve_comment.php') ?>" method="post">
                                <input type="hidden" name="comment_id" value="<?= $comment->id ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No pending comments.</p>
                <?php endif; ?>

                <?php if (!empty($pendingReplies)): ?>
                    <h2>Pending Replies</h2>
                    <?php foreach ($pendingReplies as $reply): ?>
                        <div class="reply-card p-3 mb-3">
                            <p><strong><?= htmlspecialchars($reply->first_name . ' ' . $reply->last_name) ?></strong> replied:</p>
                            <p><?= htmlspecialchars($reply->reply) ?></p>
                            <form action="<?= url('panel/review/approve_reply.php') ?>" method="post">
                                <input type="hidden" name="reply_id" value="<?= $reply->id ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No pending replies.</p>
                <?php endif; ?>
            </section>
        </section>
    </section>
</section>

<script src="<?= asset('assets/js/jquery.min.js') ?>"></script>
<script src="<?= asset('assets/js/bootstrap.min.js') ?>"></script>
</body>
</html>