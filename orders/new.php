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
                <h2 class="h-str">Добавление нового заказа на основе звонка #<?=$id_call?></h2>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="content-box">
                    <form name="new_order" action="work/add_new_order.php" method="post">
                        <input type="hidden" name="id_call" value="<?=$id_call?>">

                        <p class="text-label">Описание объекта</p>
                        <textarea name="about_object" style="width: 100%; height: 150px;" placeholder="Опишите объект..." required></textarea>

                        <div class="row">
                            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 text-left">
                                <p class="text-label">Наличие материала</p>
                                <select name="have_materials" required>
                                    <option value="0">Нет</option>
                                    <option value="1">Да</option>
                                </select>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 text-left">
                                <p class="text-label">Подготовительные работы</p>
                                <select name="preparatory" required>
                                    <option value="0">Не нужны</option>
                                    <option value="1">Нужны</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 text-left">
                                <p class="text-label">Цена. От</p>
                                <input type="text" name="price_start">
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 text-left">
                                <p class="text-label">Цена. До</p>
                                <input type="text" name="price_finish">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-left">
                                <p class="text-label">Желаемая дата осмотра</p>
                                <input type="date" name="date_view">
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-left">
                                <p class="text-label">Сроки. От</p>
                                <input type="date" name="date_work_start">
                            </div>

                            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-left">
                                <p class="text-label">Сроки. До</p>
                                <input type="date" name="date_work_finish">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5 text-left">
                                <p class="text-label">Адрес объекта</p>
                                <input type="text" name="address" id="autocomplete_street" placeholder="Введите местонахождение объекта" required>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-left">
                                <p class="text-label">Имя клиента</p>
                                <input type="text" name="client_name" id="autocomplete_name" required>
                            </div>

                            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 text-left">
                                <p class="text-label">Телефон клиента</p>
                                <input type="text" name="client_phone" id="client_phone_id" required>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5 text-left">
                                <p class="text-label">Категории специалистов</p>
                                <select id="select_categorys" name="cpec_categorys" multiple required>
                                    <?
                                    $stmt = $db->prepare("SELECT * FROM `specialist_categorys` WHERE 1");
                                    $stmt->execute();

                                    while($specialist_categorys = $stmt->fetch(PDO::FETCH_ASSOC))
                                    {
                                        ?><option value="<?=$specialist_categorys['id']?>"><?=$specialist_categorys['title']?></option><?
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-left">
                                <p class="text-label">Приоритет</p>
                                <select name="prioritet">
                                    <option value="3">Низкий</option>
                                    <option value="2" selected>Обычный</option>
                                    <option value="1">Высокий</option>
                                </select>
                            </div>

                            <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 text-left">
                                <p class="text-label">Процент</p>
                                <select name="percent">
                                    <option value="8">8%</option>
                                    <option value="9">9%</option>
                                    <option value="10" selected>10%</option>
                                    <option value="11">11%</option>
                                    <option value="12">12%</option>
                                    <option value="13">13%</option>
                                    <option value="14">14%</option>
                                    <option value="15">15%</option>
                                    <option value="16">16%</option>
                                    <option value="17">17%</option>
                                    <option value="18">18%</option>
                                    <option value="19">19%</option>
                                    <option value="20">20%</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 30px;">
                                <button type="submit" class="btn btn-success">Добавить заказ</button>
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
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/auto_complite.js"></script>
    <script>
        $(document).ready(function() {
            $('#client_phone_id').mask('+7 (999) 999-99-99');
        });


        $(function () {
            //Категории
            $('#select_categorys').searchableOptionList({
                useBracketParameters: true
            });

            // Подгружаем улицы из текстового файла:
            $.ajax({
                url: 'http://baza-remontprofi.ru/inc/get/streets.txt',
                dataType: 'json'
            }).done(function (source) {

                var countriesArray = $.map(source, function (value, key) { return { value: value, data: key }; });

                // Инициализируем autocomplete:
                $('#autocomplete_street').autocomplete({
                    lookup: countriesArray,
                    onSelect: function (suggestion) {
                        //
                    }
                });

            });

            // Подгружаем имена из текстового файла:
            $.ajax({
                url: 'http://baza-remontprofi.ru/inc/get/names.txt',
                dataType: 'json'
            }).done(function (source) {

                var countriesArray = $.map(source, function (value, key) { return { value: value, data: key }; });

                // Инициализируем autocomplete:
                $('#autocomplete_name').autocomplete({
                    lookup: countriesArray,
                    onSelect: function (suggestion) {
                        //
                    }
                });

            });

        });
    </script>

</body>
</html>

