<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?></title>
    <link rel="stylesheet" href="/resources/login/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/resources/login/javascript.js">






</head>
<body>
    <div class="login-page">
        <div class="logo">
        <img src="/resources/images/logo.png" alt="logo">
            <h3>Login</h3>
        </div>
        <div class="form">
            <form action="" method="">
                <input type="text" name="username" id="username" required placeholder="Username">
                <div class="password-container">
                    <input type="password" name="password" id="password" required placeholder="Password">
                    <i class="fa fa-eye-slash" id="togglePassword"></i>
                </div>


                <button>LOGIN</button>
                <h4>You don't have an account? <a href="">sign up here</a> </h4>
            </form>
        </div>
    </div>
    <script src="/resources/login/javascript.js"></script>
</body>
</html>