<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$title?></title>
    <link rel="stylesheet" href="/resources/login/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/resources/login/javascript.js">
    <style>
        /* Reset some basic styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }

        /* Container for the login/register page */
        .login-page {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            text-align: center;
        }

        /* Logo Section */
        .login-page .logo {
            margin-bottom: 20px;
        }

        .login-page .logo img {
            width: 100px;
            height: auto;
        }

        .login-page .logo h3 {
            font-size: 24px;
            margin-top: 10px;
            color: #555;
        }

        /* Form Container */
        .form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Input Fields */
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            color: #333;
        }

        /* Password field container */
        .password-container {
            position: relative;
        }

        .password-container i {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
        }

        /* Submit Button */
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Link to Login */
        h4 {
            font-size: 14px;
            color: #555;
        }

        h4 a {
            color: #007bff;
            text-decoration: none;
        }

        h4 a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .login-page {
                width: 90%;
                padding: 30px;
            }

            .login-page .logo h3 {
                font-size: 20px;
            }

            input[type="text"], input[type="email"], input[type="password"] {
                padding: 10px;
            }

            button {
                padding: 10px;
                font-size: 14px;
            }
        }

    </style>
</head>
<body>
<div class="login-page">
    <div class="logo">
        <img src="/resources/images/logo.png" alt="logo">
        <h3>Register</h3>
    </div>
    <div class="form">
        <form action="" method="POST">
            <!-- First Name -->
            <input type="text" name="first_name" id="first_name" required placeholder="First Name">

            <!-- Last Name -->
            <input type="text" name="last_name" id="last_name" required placeholder="Last Name">

            <!-- Email -->
            <input type="email" name="email" id="email" required placeholder="Email Address">

            <!-- Username -->
            <input type="text" name="username" id="username" required placeholder="Username">

            <!-- Phone Number -->
            <input type="text" name="phone" id="phone" required placeholder="Phone Number">

            <!-- Password -->
            <div class="password-container">
                <input type="password" name="password" id="password" required placeholder="Password">
                <i class="fa fa-eye-slash" id="togglePassword"></i>
            </div>

            <!-- Confirm Password -->
            <div class="password-container">
                <input type="password" name="password_confirm" id="password_confirm" required placeholder="Confirm Password">
                <i class="fa fa-eye-slash" id="togglePasswordConfirm"></i>
            </div>

            <!-- Submit Button -->
            <button type="submit">REGISTER</button>

            <!-- Link to Login -->
            <h4>Already have an account? <a href="login">Login here</a> </h4>
        </form>
    </div>
</div>
<script src="/resources/login/javascript.js"></script>
</body>
</html>
