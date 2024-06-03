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
            } else {
                $error = 'Password is wrong';
            }
        } else {
            $error = 'Email is wrong';
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
    <title>Otaku Blog</title>
    <link rel="stylesheet" href="<?= asset('assets/css/bootstrap.min.css') ?>" media="all" type="text/css">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>" media="all" type="text/css">
    <link rel="stylesheet" href="<?= asset('assets/css/style1.css') ?>" media="all" type="text/css">
</head>

<body>
    <section id="app">
        <?php require_once '../panel/layouts/top-nav-login.php' ?>
        <section style="height: 100vh; background-color: #5B5B5B;" class="d-flex justify-content-center align-items-center">
            <section class="form-box">
                <h1 class="bg-warning rounded-top px-2 mb-0 py-3 h5">Login</h1>
                <section class="bg-light my-0 px-2">
                    <small class="text-danger"><?php if ($error !== '') echo htmlspecialchars($error); ?></small>
                </section>
                <form class="pt-3 pb-1 px-2 bg-light rounded-bottom" action="<?= url('auth/login.php') ?>" method="post">
                    <section class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" name="email" id="email" placeholder="email ...">
                    </section>
                    <section class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="password ...">
                    </section>
                    <section class="mt-4 mb-2 d-flex justify-content-between">
                        <input type="submit" class="btn btn-success btn-sm submit-btn" value="login">
                    </section>
                    <a class="login-link" href="<?= url('auth/register.php') ?>">Register</a>
                </form>
            </section>
        </section>

    </section>
    <script src="<?= asset('assets/js/jquery.min.js') ?>"></script>
    <script src="<?= asset('assets/js/bootstrap.min.js') ?>"></script>
</body>

</html>
