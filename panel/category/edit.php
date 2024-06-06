<?php

require_once '../../functions/helpers.php';
require_once '../../functions/pdo_connection.php';
require_once '../../functions/auth.php';

$errors = [];

if (!isset($_GET['cat_id'])) {
    redirect('panel/category');
}

// Check for existing cat_id
$query = "SELECT * FROM categories WHERE id = ?;";
$statement = $pdo->prepare($query);
$statement->execute([$_GET['cat_id']]);
$category = $statement->fetch();
if ($category === false) {
    redirect('panel/category');
}

if (isset($_POST['name']) && $_POST['name'] !== '') {
    try {
        $query = "UPDATE categories SET name = ?, updated_at = NOW() WHERE id = ?";
        $statement = $pdo->prepare($query);
        $statement->execute([$_POST['name'], $_GET['cat_id']]);
        redirect('panel/category');
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) { // Unique constraint violation
            $errors['name'] = 'Category name already exists.';
        } else {
            $errors['general'] = 'An unexpected error occurred. Please try again later.';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PHP panel</title>
    <link rel="stylesheet" href="<?= asset('assets/css/bootstrap.min.css') ?>" media="all" type="text/css">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>" media="all" type="text/css">
    <style>
        .error {
            color: red;
        }
    </style>
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

                <form action="edit.php?cat_id=<?= $_GET['cat_id'] ?>" method="post">
                    <section class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" id="name" value="<?= htmlspecialchars($category->name) ?>">
                        <?php if (isset($errors['name'])): ?>
                            <small class="error"><?= $errors['name'] ?></small>
                        <?php endif; ?>
                    </section>
                    <section class="form-group">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </section>
                    <?php if (isset($errors['general'])): ?>
                        <section class="form-group">
                            <div class="error"><?= $errors['general'] ?></div>
                        </section>
                    <?php endif; ?>
                </form>

            </section>
        </section>
    </section>

</section>

<script src="<?= asset('assets/js/jquery.min.js') ?>"></script>
<script src="<?= asset('assets/js/bootstrap.min.js') ?>"></script>
</body>
</html>
