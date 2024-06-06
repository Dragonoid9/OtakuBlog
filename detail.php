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
        $stars .= $i <= $rating ? '<i class="fas fa-star text-success"></i>' : '<i class="far fa-star text-success"></i>';
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
                                    <input type="radio" name="rating" id="star5" value="5" <?= $userRating == 5 ? 'checked' : '' ?>><label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="star4" value="4" <?= $userRating == 4 ? 'checked' : '' ?>><label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="star3" value="3" <?= $userRating == 3 ? 'checked' : '' ?>><label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="star2" value="2" <?= $userRating == 2 ? 'checked' : '' ?>><label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="star1" value="1" <?= $userRating == 1 ? 'checked' : '' ?>><label for="star1" title="1 star"><i class="fas fa-star"></i></label>
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
                            <button type="submit" class="btn btn-primary mb-3">Submit Comment</button>
                        </form>
                    <?php else: ?>
                        <p>Please <a href="<?= url('auth/login.php') ?>">login</a> to leave a review.</p>
                    <?php endif; ?>
                    <!-- Display Comments and Replies -->
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
        <div class="comment-header">
            <p><strong><?= htmlspecialchars($comment->first_name . ' ' . $comment->last_name) ?></strong> commented:</p>
            <small><?= htmlspecialchars($comment->created_at) ?></small>
        </div>
        <p><?= htmlspecialchars($comment->comment) ?></p>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <form action="<?= url('panel/post/delete_comment.php') ?>" method="post" style="display:inline-block;">
                <input type="hidden" name="comment_id" value="<?= $comment->id ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
        <?php endif; ?>
        <h6>Replies</h6>
        <?php foreach ($replies[$comment->id] as $reply): ?>
            <div class="reply">
                <p><strong><?= htmlspecialchars($reply->first_name . ' ' . $reply->last_name) ?></strong> replied:</p>
                <p><?= htmlspecialchars($reply->reply) ?></p>
                <p><small><?= htmlspecialchars($reply->created_at) ?></small></p>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <form action="<?= url('panel/post/delete_reply.php') ?>" method="post" style="display:inline-block;">
                        <input type="hidden" name="reply_id" value="<?= $reply->id ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (isset($_SESSION['user'])): ?>
            <form action="<?= url('public/post_reply.php') ?>" method="post">
                <input type="hidden" name="comment_id" value="<?= $comment->id ?>">
                <textarea name="reply" class="form-control" required></textarea>
                <button type="submit" class="btn btn-primary mt-2">Reply</button>
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

<script src="<?= asset('assets/js/jquery.min.js') ?>"></script>
<script src="<?= asset('assets/js/bootstrap.min.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const starInputs = document.querySelectorAll('.star-rating input');
        starInputs.forEach(star => {
            star.addEventListener('change', function () {
                const ratingValue = this.value;
                const starsContainer = this.closest('.star-rating');
                const labels = starsContainer.querySelectorAll('label');
                labels.forEach((label, index) => {
                    if (index < ratingValue) {
                        label.style.color = '#ffc107';
                    } else {
                        label.style.color = '#ccc';
                    }
                });
            });
        });
    });
</script>
</body>
</html>
