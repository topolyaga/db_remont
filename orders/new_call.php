<?
session_start();
header("Content-Type:text/html;charset=UTF-8");

require_once("../inc/core/connectdb.php");
require_once("../inc/core/user.php");


if(!$user->isAuth())
{
    header('Location: http://'.$_SERVER['HTTP_HOST'].'/login.php');
    die();
}

//Добавление нового звонка
$stmt = $db->prepare("INSERT INTO `calls` (`date_time`, `id_manager`) VALUES (NOW(), :id_manager)");
$stmt->execute(array("id_manager"=>$_SESSION["user_id"]));

$id_call = $db->lastInsertId();


require('../inc/sys/head.php');

?>
</head>

<body>
    <? require('../inc/sys/navigation.php'); ?>

    <div class="container wrapper">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                <h2 class="h-str text-center">Звонок добавлен</h2>

                <p class="subtitle">Создать на его основе заказ?</p>

                <a href="http://<?=$_SERVER['HTTP_HOST']?>/orders/new.php?id_call=<?=$id_call?>" class="btn btn-success">Создать</a>
            </div>
        </div>
    </div>

    <? require('../inc/sys/footer.php'); ?>

<script src="http://<?=$_SERVER['HTTP_HOST']?>/js/jquery.min.js"></script>
<script src="http://<?=$_SERVER['HTTP_HOST']?>/js/bootstrap.min.js"></script>

</body>
</html>

