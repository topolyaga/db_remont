<?
session_start();
header("Content-Type:text/html;charset=UTF-8");

require_once("inc/core/connectdb.php");
require_once("inc/core/user.php");

if(!$user->isAuth())
{
    header('Location: http://'.$_SERVER['HTTP_HOST'].'/login.php');
    die();
}

$user_info = $user->GetInfo();

    require('inc/sys/head.php');

    ?>
</head>

<body>
    <? require('inc/sys/navigation.php'); ?>

    <div class="container wrapper">
        <div class="row">
            <div class="content-box col-xs-12 col-sm-12 col-md-12 col-lg-12">
                вапкпкуп
            </div>
        </div>
    </div>

    <? require('inc/sys/footer.php'); ?>

    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/jquery.min.js"></script>
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/bootstrap.min.js"></script>

</body>
</html>

