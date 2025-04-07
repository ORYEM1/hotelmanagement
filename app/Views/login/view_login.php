<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <link rel="stylesheet" href="/resources/login/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
<div class="login-page">
    <div class="logo">
        <img src="/resources/images/logo.png" alt="logo">
        <h3>Login</h3>
    </div>
    <div class="form">

        <!-- Display error message -->
        <?php if (isset($error)): ?>
            <div class="alert" style="color: red; margin-bottom: 10px;">
                <?= esc($error) ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="post" action="">
            <?= csrf_field() ?> <!-- CSRF protection -->

            <input type="text" name="username" id="username" required placeholder="Username"
                  >

            <div class="password-container">
                <input type="password" name="password" id="password" required placeholder="Password">
                <i class="fa fa-eye-slash" id="togglePassword"></i>
            </div>

            <button type="submit">LOGIN</button>

            <h4>Don't have an account? <a href="/register">Register here</a></h4>
        </form>
    </div>
</div>

<script src="/resources/login/javascript.js"></script>
</body>
</html>
