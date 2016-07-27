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

$user_info = $user->GetInfo();

$id_call = $_GET['id_call'];

require('../inc/sys/head.php');

?>
<link href="http://<?=$_SERVER['HTTP_HOST']?>/css/sol.css" rel="stylesheet">

</head>

<body>
    <? require('../inc/sys/navigation.php'); ?>

    <div class="container wrapper">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h2 class="h-str">Список звонков</h2>
            </div>
        </div>
                        <div class="content-box col-xs-12 col-sm-12 col-md-12 col-lg-12">
                
                    <table class="table table-hover table-bordered table-spec" id="spec_calls">
                                <thead>
                                    <tr>
                                        <th style="text-align: center">ID</th>
                                        <th>Дата звонка</th>
                                        <th>Менеджер</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?

                                        //Достаем имена пользователей
                                        $stmt=$db->prepare("SELECT id, name, lastname FROM `users` WHERE 1");
                                        $stmt->execute();

                                        $users_name = array();

                                        while($users_pro=$stmt->fetch(PDO::FETCH_ASSOC))
                                        {
                                            $users_name[$users_pro['id']] = $users_pro['name'].' '.$users_pro['lastname'];
                                        }

                                        $query = 'SELECT * FROM `calls` WHERE 1 ORDER BY `calls`.`id` DESC';
                                        $stmt = $db->prepare($query);
                                        $stmt->execute();

                                        while($calls = $stmt->fetch(PDO::FETCH_ASSOC))
                                        {
                                            ?>
                                            <tr>
                                                <td valign="middle" align="center" width="50" style="font-weight: bold; "><?=$calls['id']?></td>

                                                <td valign="middle" style="font-weight: bold; "><?=$calls['date_time']?></td>
                                                <td style="vertical-align: top !important; max-width: 180px;"><?=$users_name[$calls[id_manager]]?></td>
                                            </tr>
                                            <?
                                        }
                                        ?>
                                </tbody>
                            </table>
                
         </div>
    </div>



    <? require('../inc/sys/footer.php'); ?>

<script src="http://<?=$_SERVER['HTTP_HOST']?>/js/jquery.min.js"></script>
<script src="http://<?=$_SERVER['HTTP_HOST']?>/js/bootstrap.min.js"></script>
<script src="http://<?=$_SERVER['HTTP_HOST']?>/js/jquery.maskedinput.js"></script>
<script src="http://<?=$_SERVER['HTTP_HOST']?>/js/sol.js"></script>
<script src="http://<?=$_SERVER['HTTP_HOST']?>/js/auto_complite.js"></script>
    

</body>
</html>

