<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Expires" content="Fri, Jan 01 1900 00:00:00 GMT">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Lang" content="en">
    <meta name="author" content="">
    <meta http-equiv="Reply-to" content="@.com">
    <meta name="generator" content="PhpED 8.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="creation-date" content="09/06/2012">
    <meta name="revisit-after" content="15 days">


    <link href="/resources/login/style.css" rel="stylesheet">
    <script src="/resources/lib/jquery-3.7.1.min.js"></script>
    <script src="/resources/login/javascript.js"></script>

    <title>Login</title>
    <link rel="icon" href="/resources/images/icon.jpg">
</head>
<body>


<div class="login-page">
    <div id="logo">
        Mobile Pay <img src="/resources/images/logo.png" alt="Logo">
    </div>
    <div class="form">
        <form class="login-form" method="post" action="">
            <input type="text" name="username"  required placeholder="username"/>
            <input type="password" name="password" id="password" required placeholder="password"/>
            <input type="checkbox" id="toggle_password" > <label for="toggle_password">Show Password</label> <br><br>
            <button>login</button>
        </form>
        <div class="error"><?php if(isset($error)) echo $error; ?></div>
    </div>
</div>


</body>
</html>
