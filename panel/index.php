<?php
require_once '../functions/helpers.php';
require_once '../functions/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Otaku Blog Panel</title>
    <link rel="stylesheet" href="<?= asset('assets/css/bootstrap.min.css') ?>" media="all" type="text/css">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>" media="all" type="text/css">
</head>
<body>
<section id="app">

    <?php require_once 'layouts/top-nav.php'; ?>

    <section class="container-fluid">
        <section class="row">
            <section class="col-md-2 p-0">

                <?php require_once 'layouts/sidebar.php'; ?>

            </section>
            <section class="col-md-10 pb-3">

                <section style="min-height: 80vh;" class="d-flex justify-content-center align-items-center">
                    <section>
                        <h1>Welcome to Otaku Blog!</h1>
                        <h3>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h3>
                        <p>Thank you for being an integral part of our community. Your role as an admin is crucial in maintaining the quality and integrity of the Otaku Blog.</p>
                        <p>We appreciate the time and effort you put into managing content, engaging with users, and ensuring that our platform remains a welcoming place for all anime and manga enthusiasts.</p>
                        <h3>Your Impact:</h3>
                        <ul>
                            <li><strong>Inspiring Others:</strong> Your thoughtful management and interaction inspire others to contribute positively to the community.</li>
                            <li><strong>Building a Community:</strong> By curating engaging content and fostering discussions, you help build a strong, connected community.</li>
                            <li><strong>Ensuring Quality:</strong> Your vigilance in moderating content ensures that our blog maintains high standards and remains a trusted source of information.</li>
                            <li><strong>Leading by Example:</strong> Your leadership sets the tone for the entire community, encouraging respect, creativity, and passion for the world of anime and manga.</li>
                        </ul>
                        <h3>Stay Motivated!</h3>
                        <p>Remember, every post you review, every comment you moderate, and every user you support contributes to making Otaku Blog a better place. Your dedication does not go unnoticed, and we are grateful for all that you do.</p>
                        <p>Keep up the fantastic work, and let's continue to make Otaku Blog the best community for anime and manga fans!</p>
                    </section>
                </section>

            </section>
        </section>
    </section>

    
</section>

<script src="<?= asset('assets/js/jquery.min.js') ?>"></script>
<script src="<?= asset('assets/js/bootstrap.min.js') ?>"></script>
</body>
</html>
