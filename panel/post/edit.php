<?php
require_once '../../functions/helpers.php';
require_once '../../functions/pdo_connection.php';
require_once '../../functions/auth.php';

$errors = [];

if (!isset($_GET['post_id'])) {
    redirect('panel/post');
}

// Check for existing post
$query = "SELECT * FROM posts WHERE id = ?;";
$statement = $pdo->prepare($query);
$statement->execute([$_GET['post_id']]);
$post = $statement->fetch();
if ($post === false) {
    redirect('panel/post');
}

if (isset($_POST['title']) && $_POST['title'] !== ''
    && isset($_POST['cat_id']) && $_POST['cat_id'] !== ''
    && isset($_POST['body']) && $_POST['body'] !== '') {

    $query = "SELECT * FROM categories WHERE id = ?;";
    $statement = $pdo->prepare($query);
    $statement->execute([$_POST['cat_id']]);
    $category = $statement->fetch();

    try {
        if (isset($_FILES['image']) && $_FILES['image']['name'] !== '') {
            $allowedMimes = ['png', 'jpg', 'gif', 'jpeg'];
            $imageMime = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            if (!in_array($imageMime, $allowedMimes)) {
                redirect('panel/post');
            }
            $basePath = dirname(dirname(__DIR__));
            if (file_exists($basePath . $post->image)) {
                unlink($basePath . $post->image);
            }
            $image = '/assets/images/posts/' . date('Y_m_d_H_i_s') . '.' . $imageMime;
            $image_upload = move_uploaded_file($_FILES['image']['tmp_name'], $basePath . $image);
            if ($category !== false && $image_upload !== false) {
                $query = "UPDATE posts SET title = ?, cat_id = ?, body = ?, image = ?, updated_at = NOW() WHERE id = ?;";
                $statement = $pdo->prepare($query);
                $statement->execute([$_POST['title'], $_POST['cat_id'], $_POST['body'], $image, $_GET['post_id']]);
            }
        } else {
            if ($category !== false) {
                $query = "UPDATE posts SET title = ?, cat_id = ?, body = ?, updated_at = NOW() WHERE id = ?;";
                $statement = $pdo->prepare($query);
                $statement->execute([$_POST['title'], $_POST['cat_id'], $_POST['body'], $_GET['post_id']]);
            }
        }

        redirect('panel/post');

    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) { // Unique constraint violation
            $errors['title'] = 'Post title already exists.';
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

                    <form action="<?= url('panel/post/edit.php?post_id=') . $_GET['post_id'] ?>" method="post" enctype="multipart/form-data">
                        <section class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control" name="title" id="title" value="<?=  htmlspecialchars($post->title) ?>">
                            <?php if (isset($errors['title'])): ?>
                                <small class="error"><?= $errors['title'] ?></small>
                            <?php endif; ?>
                        </section>
                        <section class="form-group">
                            <label for="image">Image</label>
                            <input type="file" class="form-control" name="image" id="image">

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
                                    <option value="<?= $category->id ?>" <?php if ($category->id == $post->cat_id) echo 'selected' ?>>
                                        <?= $category->name ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </section>
                        <section class="form-group">
                            <label for="body">Body</label>
                            <textarea class="form-control" name="body" id="body" rows="5"><?= htmlspecialchars($post->body) ?></textarea>
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
    <script src="<?= asset('assets/ckeditor/ckeditor.js') ?>"></script>
    <script type="text/javascript">
        CKEDITOR.replace('body')
    </script>
</body>

</html>
