<?php
require_once '../functions/helpers.php';
require_once '../functions/pdo_connection.php';

$error = '';
if (isset($_POST['email']) && $_POST['email'] !== ''
    && isset($_POST['first_name']) && $_POST['first_name'] !== ''
    && isset($_POST['last_name']) && $_POST['last_name'] !== ''
    && isset($_POST['password']) && $_POST['password'] !== ''
    && isset($_POST['confirm']) && $_POST['confirm'] !== '') {

    if ($_POST['password'] === $_POST['confirm']) {
        if (strlen($_POST['password']) > 5) {
            $query = "SELECT * FROM users WHERE email = ?;";
            $statement = $pdo->prepare($query);
            $statement->execute([$_POST['email']]);
            $user = $statement->fetch();
            if ($user === false) {
                $query = "INSERT INTO users SET email = ?, first_name = ?, last_name = ?, password = ?, role = 'user', created_at = NOW();";
                $statement = $pdo->prepare($query);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $statement->execute([$_POST['email'], $_POST['first_name'], $_POST['last_name'], $password]);
                redirect('auth/login.php');
            } else {
                $error = 'This email already exists';
            }
        } else {
            $error = 'Password must be more than 5 characters';
        }
    } else {
        $error = 'Password does not match the confirm';
    }
} else {
    if (!empty($_POST))
        $error = 'All fields are required';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Otaku Blog - Register</title>
    <link rel="stylesheet" href="<?= asset('assets/css/bootstrap.min.css') ?>" media="all" type="text/css">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>" media="all" type="text/css">
    <link rel="stylesheet" href="<?= asset('assets/css/style1.css') ?>" media="all" type="text/css">
    <style>
    body {
            padding-top: 56px; /* Height of the navbar */
        }</style>
</head>

<body>
    <section id="app">
        <?php require_once '../panel/layouts/top-nav-login.php' ?>
        <section class="d-flex justify-content-center align-items-center" style="height: 100vh;">
            <section class="form-box">
                <h1>Register with Us</h1>
                <?php if ($error !== ''): ?>
                    <div class="text-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form action="<?= url('auth/register.php') ?>" method="post">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email">
                    </div>
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" class="form-control" name="first_name" id="first_name" placeholder="Enter your first name">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Enter your last name">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password">
                    </div>
                    <div class="form-group">
                        <label for="confirm">Confirm Password</label>
                        <input type="password" class="form-control" name="confirm" id="confirm" placeholder="Confirm your password">
                    </div>
                    <button type="submit" class="btn">Register</button>
                    <a class="login-link" href="<?= url('auth/login.php') ?>">Already a User? Login</a>
                </form>
            </section>
        </section>
    </section>
    <script src="<?= asset('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= asset('assets/js/bootstrap.min.js') ?>"></script>
</body>

</html>
