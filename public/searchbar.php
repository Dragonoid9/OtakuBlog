<?php
require_once 'functions/helpers.php';
require_once 'functions/pdo_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BitBlog Category</title>
    <link rel="stylesheet" href="<?= asset('assets/css/bootstrap.min.css')?>" media="all" type="text/css">
    <link rel="stylesheet" href="<?=asset('assets/css/style.css')?>" media="all" type="text/css">
</head>
<body>
    <section id="app">
        <?php $query = isset($_GET['query']) ? $_GET['query'] : '';
    $searchResults = [];

    if ($query !== '') {
    $searchQuery = '%' . $query . '%';
    $sql = "SELECT * FROM posts WHERE (title LIKE ? OR body LIKE ?) AND status = 10";
    $statement = $pdo->prepare($sql);
    $statement->execute([$searchQuery, $searchQuery]);
    $searchResults = $statement->fetchAll();
}?>
        <?php require_once "layouts/top-nav.php"; ?>
        <section class="container my-3">
            <form class="form-inline my-2 my-lg-0 w-100" action="<?=$_SERVER['PHP_SELF']?>" method="get">
                <input class="form-control mr-sm-2 flex-grow-1" type="search" placeholder="Search" aria-label="Search" name="query">
                <button class="btn btn-primary my-2 my-sm-0" type="submit">Search</button>
            </form>
        </section>


        <section id="app">
            <section class="container my-5">
                <h1>Results for your search</h1>
                <hr>
                <section class="row">
                    <?php if (!empty($searchResults)) : ?>
                        <?php foreach ($searchResults as $post) : ?>
                            <section class="col-md-4">
                                <section class="mb-2 overflow-hidden" style="height: 15rem;">
                                    <img class="img-fluid" src="<?= asset($post->image) ?>" style="height:200px; width:400px;" alt="Failed">
                                </section>
                                <h2 class="h5 text-truncate"><?= htmlspecialchars($post->title) ?></h2>
                                <p><?= substr($post->body, 0, 80) ?></p>
                                <p><a class="btn btn-primary" href="<?= url('detail.php?post_id=') . $post->id ?>" role="button">View details »</a></p>
                            </section>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <section class="col-12">
                            <p>No results found for your serach.</p>
                        </section>
                    <?php endif; ?>
                </section>
            </section>
        </section>
</body>
</html>