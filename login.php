<?php
session_start();
header("Content-Type:text/html;charset=UTF-8");

require_once("inc/core/connectdb.php");
require_once("inc/core/user.php");

if($user->isAuth())
{
    header('Location: http://'.$_SERVER['HTTP_HOST']);
    die();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <title>Авторизация</title>

    <link href="http://<?=$_SERVER['HTTP_HOST']?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://<?=$_SERVER['HTTP_HOST']?>/css/style.css" rel="stylesheet">
    <link href="http://<?=$_SERVER['HTTP_HOST']?>/css/icons.css" rel="stylesheet">

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/jquery.min.js"></script>
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/bootstrap.min.js"></script>
    <script type="text/JavaScript" src="http://<?=$_SERVER['HTTP_HOST']?>/js/userauth.js"></script>

</head>

<body style="background: #FFFFFF">
    <div style="position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%); text-align: center; width: 400px;">


        <p style="margin: 20px 0 20px 0; font-size: 24px;" align="center">ВХОД</p>

        <form name="user_auth" method="post" id="form_auth" action="javascript:void(null);" onsubmit="UserAuth();">
            <input type="text" name="user_logname" style="width:90%; max-width:300px; margin:0 auto" placeholder="Введите логин" required>
            <input type="password" name="user_pass" style="width:90%; max-width:300px; margin:10px auto" placeholder="Введите пароль" required>

            <div style="margin:10px auto; text-align:center">
                <button class="btn btn-success" type="submit" style="width:90%; max-width:300px; margin:0 auto">ВОЙТИ</button>

                <div id="loading-user-auth" class="loading" title="Вход"></div>
                <div id="result-user-auth" class="system-messege co_777" style="max-width: 700px; margin: 20px auto;"></div>
            </div>
        </form>
    </div>
</body>
</html>