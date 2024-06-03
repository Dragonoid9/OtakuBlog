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
    <link rel="stylesheet" href="<?=asset('assets/css/bootstrap.min.css')?>" media="all" type="text/css">
    <link rel="stylesheet" href="<?=asset('assets/css/style.css')?>" media="all" type="text/css">
</head>
<body>
<section id="app">
<?php require_once "layouts/top-nav.php"?>

<!-- Search Bar -->
<section class="container my-3">
        <form class="form-inline my-2 my-lg-0 w-100" action="searchbar.php" method="get">
            <input class="form-control mr-sm-2 flex-grow-1" type="search" placeholder="Search" aria-label="Search" name="query">
            <button class="btn btn-primary my-2 my-sm-0" type="submit">Search</button>
        </form>
</section>

    <section class="container my-5">
    <?php
$notFound = false;

if (isset($_GET['cat_id']) && $_GET['cat_id'] !== '') {
    //check for exist cat_id
    $query = "SELECT * FROM categories WHERE id = ?;";
    $statement = $pdo->prepare($query);
    $statement->execute([$_GET['cat_id']]);
    $category = $statement->fetch();
    if ($category !== false) {?>
                  <section class="row">
                <section class="col-12">
                    <h1><?=$category->name?></h1>
                    <hr>
                </section>
            </section>
            <section class="row">
                    <?php
$query = "SELECT posts.* FROM categories JOIN posts ON categories.id = posts.cat_id WHERE categories.id = ? AND posts.status = 10 ;";
        $statement = $pdo->prepare($query);
        $statement->execute([$_GET['cat_id']]);
        $posts = $statement->fetchAll();
        foreach ($posts as $post) {
            ?>
               <section class="col-md-4">
               <section class="mb-2 overflow-hidden" style="height: 15rem;"><img class="img-fluid" src="<?=asset($post->image)?>" style="height:200px; width:400px;" alt=""></section>
                    <h2 class="h5 text-truncate"><?=$post->title?></h2>
                    <p><?=substr($post->body, 0, 80)?></p>
                    <p><a class="btn btn-primary" href="<?=url('detail.php?post_id=') . $post->id?>" role="button">View details »</a></p>
               </section>
               <?php
}?>

       </section>
           <?php } else {
        $notFound = true;
    }
} else {
    $notFound = true;
}

?>


          <?php
if ($notFound) {
    ?>
            <section class="row">
                <section class="col-12">
                    <h1>Category not found</h1>
                </section>
            </section>

            <?php }?>

        </section>
    </section>

</section>
<script src="<?=asset('assets/js/jquery.min.js')?>"></script>
<script src="<?=asset('assets/js/bootstrap.min.js')?>"></script>
</body>
</html>