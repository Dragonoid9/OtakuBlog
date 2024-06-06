<?php
require_once 'functions/helpers.php';
require_once 'functions/pdo_connection.php';

// Define how many results you want per page
$results_per_page = 9;

// Find out the number of results stored in database
$query = "SELECT COUNT(*) FROM posts WHERE status = 10";
$statement = $pdo->prepare($query);
$statement->execute();
$total_results = $statement->fetchColumn();

// Determine number of total pages available
$total_pages = ceil($total_results / $results_per_page);

// Determine which page number visitor is currently on
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Determine the SQL LIMIT starting number for the results on the displaying page
$start_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from database 
$query = "SELECT * FROM posts WHERE status = 10 LIMIT $start_limit, $results_per_page";
$statement = $pdo->prepare($query);
$statement->execute();
$posts = $statement->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Otaku Blog</title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/bootstrap.min.css'); ?>" media="all" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>" media="all" type="text/css">
    <style>
    body {
        padding-top: 56px; /* Height of the navbar */
    }
    </style>
</head>
<body>
<section id="app">

    <?php require_once "layouts/top-nav.php"; ?>
    
    <!-- Search Bar -->
    <section class="container my-3">
        <form class="form-inline my-2 my-lg-0 w-100" action="searchbar.php" method="get">
            <input class="form-control mr-sm-2 flex-grow-1" type="search" placeholder="Search" aria-label="Search" name="query">
            <button class="btn btn-primary my-2 my-sm-0" type="submit">Search</button>
        </form>
    </section>
    <hr>
    <section class="container my-5">
        <!-- Example row of columns -->
        <section class="row">
            <?php foreach ($posts as $post) { ?>
                <section class="col-md-4">
                    <section class="mb-2 overflow-hidden" style="height: 15rem;">
                        <img class="img-fluid" src="<?= asset($post->image) ?>" style="height:200px; width:350px;" alt="">
                    </section>
                    <h2 class="h5 text-truncate"><?= $post->title ?></h2>
                    <p><?= substr($post->body, 0, 80) ?></p>
                    <p><a class="btn btn-primary" href="<?php echo url('detail.php?post_id=') . $post->id ?>" role="button">View details »</a></p>
                </section>
            <?php } ?>
        </section>

        <!-- Pagination Controls -->
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="index.php?page=<?= $page - 1; ?>">Previous</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : ''; ?>"><a class="page-link" href="index.php?page=<?= $i; ?>"><?= $i; ?></a></li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="index.php?page=<?= $page + 1; ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </section>

</section>
<script src="<?php echo asset('assets/js/jquery.min.js'); ?>"></script>
<script src="<?php echo asset('assets/js/bootstrap.min.js'); ?>"></script>
</body>
</html>
