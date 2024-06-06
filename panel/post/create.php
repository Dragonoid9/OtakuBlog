<?php
require_once '../../functions/helpers.php';
require_once '../../functions/pdo_connection.php';
require_once '../../functions/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['title'])) {
        $errors['title'] = 'Title is required.';
    }

    if (empty($_POST['cat_id'])) {
        $errors['cat_id'] = 'Category is required.';
    }

    if (empty($_POST['body'])) {
        $errors['body'] = 'Body is required.';
    }

    if (!isset($_FILES['image']) || $_FILES['image']['name'] === '') {
        $errors['image'] = 'Image is required.';
    }

    if (empty($errors)) {
        try {
            $query = "SELECT * FROM categories WHERE id = ?;";
            $statement = $pdo->prepare($query);
            $statement->execute([$_POST['cat_id']]);
            $category = $statement->fetch();

            $allowedMimes = ['png', 'jpg', 'gif', 'jpeg'];
            $imageMime = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            if (!in_array($imageMime, $allowedMimes)) {
                redirect('panel/post');
            }

            $basePath = dirname(dirname(__DIR__));
            $image = '/assets/images/posts/' . date('Y_m_d_H_i_s') . '.' . $imageMime;
            $image_upload = move_uploaded_file($_FILES['image']['tmp_name'], $basePath . $image);
            if ($category !== false && $image_upload !== false) {
                $query = "INSERT INTO posts SET title = ?, cat_id = ?, body = ?, image = ?, created_at = NOW() ;";
                $statement = $pdo->prepare($query);
                $statement->execute([$_POST['title'], $_POST['cat_id'], $_POST['body'], $image]);
                redirect('panel/post');
            }
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Unique constraint violation
                $errors['title'] = 'A post with this title already exists.';
            } else {
                $errors['general'] = 'An unexpected error occurred. Please try again later.';
            }
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

                <form action="<?= url('panel/post/create.php') ?>" method="post" enctype="multipart/form-data">
                    <section class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" name="title" id="title" placeholder="title ...">
                        <?php if (isset($errors['title'])): ?>
                            <small class="error"><?= $errors['title'] ?></small>
                        <?php endif; ?>
                    </section>
                    <section class="form-group">
                        <label for="image">Image</label>
                        <input type="file" class="form-control" name="image" id="image">
                        <?php if (isset($errors['image'])): ?>
                            <small class="error"><?= $errors['image'] ?></small>
                        <?php endif; ?>
                    </section>
                    <section class="form-group">
                        <label for="cat_id">Category</label>
                        <select class="form-control" name="cat_id" id="cat_id">
                            <?php
                            $query = "SELECT * FROM categories;";
                            $statement = $pdo->prepare($query);
                            $statement->execute();
                            $categories = $statement->fetchAll();

                            foreach ($categories as $category) { ?>
                                <option value="<?= $category->id ?>"><?= $category->name ?></option>
                            <?php } ?>
                        </select>
                        <?php if (isset($errors['cat_id'])): ?>
                            <small class="error"><?= $errors['cat_id'] ?></small>
                        <?php endif; ?>
                    </section>
                    <section class="form-group">
                        <label for="body">Body</label>
                        <textarea class="form-control" name="body" id="body" rows="5" placeholder="body ..."></textarea>
                        <?php if (isset($errors['body'])): ?>
                            <small class="error"><?= $errors['body'] ?></small>
                        <?php endif; ?>
                    </section>
                    <section class="form-group">
                        <button type="submit" class="btn btn-primary">Create</button>
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

<script src="<?= asset('assets/ckeditor/ckeditor.js') ?>"></script>
<script type="text/javascript">
    CKEDITOR.replace('body')
</script>

</body>
</html>
