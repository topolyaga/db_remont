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

require('../inc/sys/head.php');

?>
<link href="http://<?=$_SERVER['HTTP_HOST']?>/css/sol.css" rel="stylesheet">

</head>

<body>
    <? require('../inc/sys/navigation.php'); ?>

    <div class="container wrapper">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h2 class="h-str">Добавление нового специалиста</h2>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="content-box">
                    <form name="new_specialist" action="work/add_new_specialist.php" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                            <p class="text-label">Имя</p>
                            <input type="text" name="name" placeholder="Введите имя" required>
                        </div>
                        <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                            <p class="text-label">Фамилия</p>
                            <input type="text" name="lastname" placeholder="Введите фамилию" required>
                        </div>
                        <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                            <p class="text-label">Аватарка</p>
                            <input type="file" name="avatar">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                            <p class="text-label">Телефон 1</p>
                            <input type="text" name="phone" id="phone1" placeholder="Введите номер" required>
                        </div>
                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                            <p class="text-label">Телефон 2</p>
                            <input type="text" name="phone2" id="phone2" placeholder="Введите номер">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <p class="text-label">Категории</p>
                            <select id="select_categorys" name="categorys" multiple required>
                                <?
                                $stmt = $db->prepare("SELECT * FROM `specialist_categorys` WHERE 1 ORDER BY  `specialist_categorys`.`title` ASC");
                                $stmt->execute();

                                while($specialist_categorys = $stmt->fetch(PDO::FETCH_ASSOC))
                                {
                                    ?><option value="<?=$specialist_categorys['id']?>"><?=$specialist_categorys['title']?></option><?
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                        <div class="row">
                            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                                <p class="text-label">Мин. цена в руб</p>
                                <input type="text" name="min_price" placeholder="Введитe цену" required>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                                <p class="text-label">Мин. цена при объеме в руб</p>
                                <input type="text" name="min_price_opt" placeholder="Введите цену" required>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                                <p class="text-label">Мин. заказ в руб</p>
                                <input type="text" name="min_price_for_order" placeholder="Введите стоимость" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                                <p class="text-label">Опыт работы с (года)</p>
                                <input type="number" name="experience" required>
                            </div>
                            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                                <p class="text-label">Работа по договору</p>
                                <select name="contract" required>
                                    <option value="1">Да</option>
                                    <option value="0">Нет</option>
                                </select>
                            </div>
                            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                                <p class="text-label">Гарантия</p>
                                <select name="guarantee" required>
                                    <option value="1">Да</option>
                                    <option value="0">Нет</option>
                                </select>
                            </div>
                            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
                                <p class="text-label">Увеличение команды</p>
                                <select name="scale" required>
                                    <option value="1">Да</option>
                                    <option value="0">Нет</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <p class="text-label">Приоритетные районы работы</p>
                                <input type="text" name="areas" placeholder="Введитe приоритетные районы работы через запятую">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                <p class="text-label">Комментарий</p>
                                <textarea name="comment" style="width: 100%; height: 150px;" placeholder="Введите дополнительные примечания, пожелания и т.д." required></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 30px;">
                                <button type="submit" class="btn btn-success">Добавить специалиста</button>
                            </div>
                        </div>
                    </form>
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

