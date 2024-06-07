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
    // Fetch ratings
    $query = "SELECT AVG(rating) as aggregateRating, COUNT(*) as totalReviews FROM ratings WHERE post_id = ?";
    $statement = $pdo->prepare($query);
    $statement->execute([$post_id]);
    $ratingData = $statement->fetch();
    $aggregateRating = $ratingData->aggregateRating;
    $totalReviews = $ratingData->totalReviews;

    // Fetch comments
    $query = "SELECT comments.*, users.first_name, users.last_name FROM comments
              JOIN users ON comments.user_id = users.id
              WHERE post_id = ? AND comments.approved = TRUE ORDER BY created_at DESC";
    $statement = $pdo->prepare($query);
    $statement->execute([$post_id]);
    $comments = $statement->fetchAll();

    // Fetch replies for each comment
    $replies = [];
    foreach ($comments as $comment) {
        $query = "SELECT replies.*, users.first_name, users.last_name FROM replies 
                  JOIN users ON replies.user_id = users.id WHERE comment_id = ? AND replies.approved = TRUE ORDER BY created_at ASC";
        $statement = $pdo->prepare($query);
        $statement->execute([$comment->id]);
        $replies[$comment->id] = $statement->fetchAll();
    }

    // Check if the user has already rated this post
    $user_id = $_SESSION['user_id'] ?? null;
    $query = "SELECT rating FROM ratings WHERE post_id = ? AND user_id = ?";
    $statement = $pdo->prepare($query);
    $statement->execute([$post_id, $user_id]);
    $userRating = $statement->fetchColumn();
}

function displayStars($rating, $maxRating = 5) {
    $stars = '';
    for ($i = 1; $i <= $maxRating; $i++) {
        $class = $i <= $rating ? 'fas fa-star text-warning rated' : 'far fa-star text-warning';
        $stars .= '<i class="' . $class . '" style="font-size: ' . ($i <= $rating ? '2em' : '1em') . ';"></i>';
    }
    return $stars;
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
    <link rel="stylesheet" href="<?= asset('assets/css/review.css') ?>" media="all" type="text/css">
    <link rel="stylesheet" href="<?= asset('assets/css/bootstrap1.min.css') ?>" media="all" type="text/css">
</head>
<body>
<section id="app">
    <?php require_once "layouts/top-nav.php" ?>

    <section class="container my-5">
        <section class="row">
            <section class="col-md-12">
                <?php if ($post !== false): ?>
                    <h1><?= htmlspecialchars($post->title) ?></h1>
                    <h5 class="d-flex justify-content-between align-items-center">
                        <a href="<?= url('category.php?cat_id=') . $post->cat_id ?>"><?= htmlspecialchars($post->category_name) ?> Category:</a>
                        <span class="date-time"><?= htmlspecialchars($post->created_at) ?></span>
                    </h5>
                    <article class="bg-article p-3">
                        <img class="float-right mb-2 ml-2" style="width: 18rem;" src="<?= asset($post->image) ?>" alt="">
                        <?= ($post->body) ?>
                    </article>
                    <h2 class="aggregate-rating">Aggregate Rating: <?= number_format($aggregateRating, 1) ?> / 5 (<?= $totalReviews ?> reviews)</h2>
                    <div class="aggregate-rating">
                        <?= displayStars(round($aggregateRating)) ?>
                    </div>
                    <?php if (isset($_SESSION['user'])): ?>
                        <h2>Leave a Rating</h2>
                        <form action="<?= url('public/post_rating.php') ?>" method="post">
                            <input type="hidden" name="post_id" value="<?= $post->id ?>">
                            <div class="form-group">
                                <label for="rating">Rating</label>
                                <div class="star-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" <?= $userRating == $i ? 'checked' : '' ?>>
                                        <label for="star<?= $i ?>" title="<?= $i ?> stars" data-value="<?= $i ?>"><i class="fas fa-star"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mb-3">Submit Rating</button>
                        </form>

                        <h2>Leave a Comment</h2>
                        <form action="<?= url('public/post_comment.php') ?>" method="post">
                            <input type="hidden" name="post_id" value="<?= $post->id ?>">
                            <div class="form-group">
                                <label for="comment">Comment</label>
                                <textarea name="comment" class="form-control" id="comment"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary submit-comment-btn">Submit Comment</button>
                        </form>
                    <?php else: ?>
                        <p>Please <a href="<?= url('auth/login.php') ?>">login</a> to leave a review.</p>
                   
                        <?php endif; ?>
                    <!-- Display Comments and Replies -->
<div class="comments-box">
    <h2 class="comment-section">Comments</h2>
    <?php foreach ($comments as $comment): ?>
        <div class="comment">
            <div class="comment-header">
                <strong><?= htmlspecialchars($comment->first_name . ' ' . $comment->last_name) ?></strong>
                <small><?= htmlspecialchars($comment->created_at) ?></small>
            </div>
            <p><?= htmlspecialchars($comment->comment) ?></p>
            <div class="comment-actions">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <form action="<?= url('panel/post/delete_comment.php') ?>" method="post" style="display:inline-block;">
                        <input type="hidden" name="comment_id" value="<?= $comment->id ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                <?php endif; ?>
            </div>
            <h6>Replies</h6>
            <?php foreach ($replies[$comment->id] as $reply): ?>
                <div class="reply">
                    <div class="reply-header">
                        <strong><?= htmlspecialchars($reply->first_name . ' ' . $reply->last_name) ?></strong>
                        <small><?= htmlspecialchars($reply->created_at) ?></small>
                    </div>
                    <p><?= htmlspecialchars($reply->reply) ?></p>
                    <div class="reply-actions">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <form action="<?= url('panel/post/delete_reply.php') ?>" method="post" style="display:inline-block;">
                                <input type="hidden" name="reply_id" value="<?= $reply->id ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (isset($_SESSION['user'])): ?>
                <form action="<?= url('public/post_reply.php') ?>" method="post">
                    <input type="hidden" name="comment_id" value="<?= $comment->id ?>">
                    <div class="form-group">
                        <textarea name="reply" class="form-control" required placeholder="Write a reply..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2">Reply</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
    <section>Post not found!</section>
<?php endif; ?>
</section>
</section>
</section>
</section>

<script src="<?= asset('assets/js/jquery.min.js') ?>"></script>
<script src="<?= asset('assets/js/bootstrap.min.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const stars = document.querySelectorAll('.star-rating label');
        const starInputs = document.querySelectorAll('.star-rating input');

        function highlightStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.querySelector('i').style.color = '#ffc107';
                    star.querySelector('i').style.fontSize = '2em';
                } else {
                    star.querySelector('i').style.color = '#ccc';
                    star.querySelector('i').style.fontSize = '1em';
                }
            });
        }

        starInputs.forEach(starInput => {
            starInput.addEventListener('change', function () {
                const ratingValue = this.value;
                highlightStars(ratingValue);
            });
        });

        stars.forEach(star => {
            star.addEventListener('mouseover', function () {
                const ratingValue = this.getAttribute('data-value');
                highlightStars(ratingValue);
            });

            star.addEventListener('mouseout', function () {
                const checkedInput = document.querySelector('.star-rating input:checked');
                const ratingValue = checkedInput ? checkedInput.value : 0;
                highlightStars(ratingValue);
            });
        });

        // Set initial highlight based on checked input
        const checkedInput = document.querySelector('.star-rating input:checked');
        if (checkedInput) {
            highlightStars(checkedInput.value);
        }
    });
    </script>
</body>
</html>