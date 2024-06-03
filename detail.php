<?php
session_start();
require_once 'functions/helpers.php';
require_once 'functions/pdo_connection.php';

$post_id = $_GET['post_id'];

// Fetch post details
$query = "SELECT posts.*, categories.name AS category_name FROM posts JOIN categories ON posts.cat_id = categories.id WHERE posts.id = ? AND posts.status = 10";
$statement = $pdo->prepare($query);
$statement->execute([$post_id]);
$post = $statement->fetch();

if ($post !== false) {
    // Fetch reviews for the post
    $query = "SELECT reviews.*, users.first_name, users.last_name FROM reviews 
              JOIN users ON reviews.user_id = users.id WHERE post_id = ? ORDER BY created_at DESC";
    $statement = $pdo->prepare($query);
    $statement->execute([$post_id]);
    $reviews = $statement->fetchAll();

    // Calculate aggregate rating
    $totalReviews = count($reviews);
    $aggregateRating = $totalReviews ? array_sum(array_column($reviews, 'rating')) / $totalReviews : 0;

    // Fetch replies for each review
    $replies = [];
    foreach ($reviews as $review) {
        $query = "SELECT replies.*, users.first_name, users.last_name FROM replies 
                  JOIN users ON replies.user_id = users.id WHERE review_id = ? ORDER BY created_at ASC";
        $statement = $pdo->prepare($query);
        $statement->execute([$review->id]);
        $replies[$review->id] = $statement->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Otaku Blog</title>
    <link rel="stylesheet" href="<?= asset('assets/css/bootstrap.min.css') ?>" media="all" type="text/css">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>" media="all" type="text/css">
</head>
<body>
<section id="app">
    <?php require_once "layouts/top-nav.php"?>

    <section class="container my-5">
        
        <section class="row">
            <section class="col-md-12">
                <?php if ($post !== false): ?>
                    <h1><?= htmlspecialchars($post->title) ?></h1>
                    <h5 class="d-flex justify-content-between align-items-center">
                        <a href="<?= url('category.php?cat_id=') . $post->cat_id ?>"><?= htmlspecialchars($post->category_name) ?>Category:</a>
                        <span class="date-time"><?= htmlspecialchars($post->created_at) ?></span>
                    </h5>
                    <article class="bg-article p-3">
                        <img class="float-right mb-2 ml-2" style="width: 18rem;" src="<?= asset($post->image) ?>" alt="">
                        <?= htmlspecialchars($post->body) ?>
                    </article>
                    <h2>Aggregate Rating: <?= number_format($aggregateRating, 1) ?> / 5 (<?= $totalReviews ?> reviews)</h2>
                    <?php if (isset($_SESSION['user'])): ?>
                        <h2>Leave a Review</h2>
                        <form action="<?= url('public/post_review.php') ?>" method="post">
                            <input type="hidden" name="post_id" value="<?= $post->id ?>">
                            <div class="form-group">
                                <label for="rating">Rating</label>
                                <select name="rating" class="form-control" id="rating" required>
                                    <option value="1">1 star</option>
                                    <option value="2">2 stars</option>
                                    <option value="3">3 stars</option>
                                    <option value="4">4 stars</option>
                                    <option value="5">5 stars</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="comment">Comment</label>
                                <textarea name="comment" class="form-control" id="comment" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    <?php else: ?>
                        <p>Please <a href="<?= url('auth/login.php') ?>">login</a> to leave a review.</p>
                    <?php endif; ?>
                    <!-- Display Reviews and Replies -->
                    <?php foreach ($reviews as $review): ?>
                        <div class="review">
                            <p><strong><?= htmlspecialchars($review->first_name . ' ' . $review->last_name) ?></strong> rated <?= htmlspecialchars($review->rating) ?> stars</p>
                            <p><?= htmlspecialchars($review->comment) ?></p>
                            <p><small><?= htmlspecialchars($review->created_at) ?></small></p>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <form action="<?= url('panel/post/delete_comment.php') ?>" method="post">
                                    <input type="hidden" name="review_id" value="<?= $review->id ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            <?php endif; ?>
                            <h3>Replies</h3>
                            <?php foreach ($replies[$review->id] as $reply): ?>
                                <div class="reply">
                                    <p><strong><?= htmlspecialchars($reply->first_name . ' ' . $reply->last_name) ?></strong> replied</p>
                                    <p><?= htmlspecialchars($reply->comment) ?></p>
                                    <p><small><?= htmlspecialchars($reply->created_at) ?></small></p>
                                </div>
                            <?php endforeach; ?>
                            <?php if (isset($_SESSION['user'])): ?>
                                <form action="<?= url('public/post_reply.php') ?>" method="post">
                                    <input type="hidden" name="review_id" value="<?= $review->id ?>">
                                    <input type="hidden" name="post_id" value="<?= $post->id ?>">
                                    <textarea name="comment" class="form-control" required></textarea>
                                    <button type="submit" class="btn btn-primary">Reply</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <section>Post not found!</section>
                <?php endif; ?>
            </section>
        </section>
    </section>

</section>

</body>
</html>
