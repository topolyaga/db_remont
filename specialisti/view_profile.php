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

$id_specialist = $_GET['id'];

$user_info = $user->GetInfo();

$stmt=$db->prepare("SELECT * FROM `specialist_db` WHERE id=:id_specialist");
$stmt->execute(array ("id_specialist"=>$id_specialist));
$specialist = $stmt->fetch(PDO::FETCH_ASSOC);

require('../inc/sys/head.php');

?>
<link href="http://<?=$_SERVER['HTTP_HOST']?>/css/sol.css" rel="stylesheet">

</head>

<body>
    <? require('../inc/sys/navigation.php'); ?>

    <div class="container wrapper">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h2 class="h-str">Профиль специалиста</h2>
            </div>

            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                <div class="content-box" style="padding: 0 15px 0 15px;">
                    <div class="row">
                        <?
                        switch($specialist['status'])
                        {
                            case 'Свободен': echo '<div class="sp-status tss-free">Свободен</div>'; break;
                            case 'Занят. Н': echo '<div class="sp-status tss-busy">Занят. Н</div>'; break;
                            case 'Занят. Д': echo '<div class="sp-status tss-busy">Занят. Д</div>'; break;
                            case 'На осмотре': echo '<div class="sp-status tss-view">На осмотре</div>'; break;
                            case 'Выходной': echo '<div class="sp-status tss-output">Выходной</div>'; break;
                            case 'Под подозрением': echo '<div class="sp-status tss-suss">Под подозрением</div>'; break;
                            case 'Черный список': echo '<div class="sp-status tss-bl">Черный сапвспиписок</div>'; break;
                        }
                        ?>
                        
                            <img id="spec_avatar" src="<?=$specialist[avatar_link]?>" class="img-responsive avatar">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <p class="text-label"><?=$specialist['name'].' '.$specialist['lastname']?></p>
                            <p class="phone"><?=$specialist['phone']?></p>
                            <p class="phone"><?=$specialist['phone2']?></p>

                            <?
                            $stmt1=$db->prepare("SELECT * FROM `specialist_categorys` WHERE 1");
                            $stmt1->execute();

                            $category_titles = array();

                            while($category=$stmt1->fetch(PDO::FETCH_ASSOC))
                            {

                                $category_titles[$category['id']] = $category['title'];
                            }

                            $ids_categorys = explode(",", $specialist['id_categorys']);

                            $str_categorys = '';

                            foreach($ids_categorys as $val)
                            {
                                $str_categorys.=$category_titles[$val].', ';
                            }

                            $str_categorys = substr($str_categorys, 0, -2);

                            echo $str_categorys;
                            ?>
                            <?
                            $stmt2=$db->prepare("SELECT SUM(value)/count(id) as rating FROM `specialist_rating` WHERE id_specialist=:id_specialist");
                            $stmt2->execute(array("id_specialist"=>$specialist['id']));
                            $rating=$stmt2->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <p style="font-size: 18px; color: #f0ad4e;"><i class="icon-starfull"></i><?
                            echo round($rating['rating'], 2);
                            ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
                <div class="content-box">
                    <div class="row">

                    </div>
                </div>
            </div>
        </div>
    </div>

    <? require('../inc/sys/footer.php'); ?>

<script src="http://<?=$_SERVER['HTTP_HOST']?>/js/jquery.min.js"></script>
<script src="http://<?=$_SERVER['HTTP_HOST']?>/js/bootstrap.min.js"></script>
<script src="http://<?=$_SERVER['HTTP_HOST']?>/js/jquery.maskedinput.js"></script>
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/sol.js"></script>
    <script>
        $(document).ready(function() {
            $('#phone1').mask('8 (999) 999-99-99');
            $('#phone2').mask('8 (999) 999-99-99');
        });

        $(function () {
            //Категории
            $('#select_categorys').searchableOptionList({
                useBracketParameters: true
            });
        });
    </script>

</body>
</html>

