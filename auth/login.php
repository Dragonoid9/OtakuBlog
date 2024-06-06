<?php
session_start();
require_once '../functions/helpers.php';
require_once '../functions/pdo_connection.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {

        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        $query = "SELECT * FROM users WHERE email = ?";
        $statement = $pdo->prepare($query);
        $statement->execute([$email]);
        $user = $statement->fetch();

        if ($user !== false) {
            if (password_verify($password, $user->password)) {
                $_SESSION['user'] = $user->email;
                $_SESSION['user_id'] = $user->id; // Set the user_id in session
                $_SESSION['username'] = $user->first_name . ' ' . $user->last_name;
                $_SESSION['role'] = $user->role;
                if ($user->role == 'admin') {
                    redirect('panel/index.php');
                } else {
                    redirect('index.php');
                }
            } 
        } else {
            $error = 'Email or Password is incorrect.';
        }
    } 
    else {
        $error = 'All fields are required';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Otaku Blog - Login</title>
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
                <h1>Login</h1>
                <?php if ($error !== ''): ?>
                    <div class="text-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form action="<?= url('auth/login.php') ?>" method="post">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password">
                    </div>
                    <button type="submit" class="btn">Login</button>
                    <a class="login-link" href="<?= url('auth/register.php') ?>">Not a User? Sign up</a>
                </form>
            </section>
        </section>
    </section>
    <script src="<?= asset('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= asset('assets/js/bootstrap.min.js') ?>"></script>
</body>

</html>
