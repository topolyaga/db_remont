
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

//

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
                <h2 class="h-str">Добавление нового объявления для Авито</h2>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="content-box">
                        <div class="row">
                            <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
                                <p class="text-label">Заголовок объявления</p>
                                <input type="text" name="title" placeholder="Введите заголовок объявления" required onchange="firstValidate(this);">

                                <p class="text-label">Текст объявления</p>
                                <textarea id="ta_description" name="description" style="width: 100%; height: 150px; background-position: top 10px right;" placeholder="Опишите объявление" required onchange="firstValidate(this);"></textarea>

                                <p class="text-label">Цена</p>
                                <input type="number" name="price" required onchange="firstValidate(this);">
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                                <p class="text-label">Выберите категорию</p>
                                <select id="id_category" name="category" required onchange="firstValidate(this);">
                                    <option value="0">Выбрать категорию</option>
                                    <?
                                    $stmt = $db->prepare("SELECT * FROM `ads_categorys` WHERE 1 ORDER BY  `ads_categorys`.`title` ASC");
                                    $stmt->execute();

                                    while($ads_categorys = $stmt->fetch(PDO::FETCH_ASSOC))
                                    {
                                        ?><option value="<?=$ads_categorys['id']?>"><?=$ads_categorys['title']?></option><?
                                    }
                                    ?>
                                </select>

                                <p class="text-label">Выберите дату публикации</p>
                                <input type="date" name="public_date" required onchange="firstValidate(this);">

                                <p class="text-label">Статус</p>
                                <select id="id_status" name="status" required>
                                    <option value="Черновик">Черновик</option>
                                    <option value="Готово к публикации" selected>Готово к публикации</option>
                                </select>

                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 20px;">
                                        <div id="loading_add_ad" class="loading" title="Добавление"></div>

                                        <button type="button" class="btn btn-success" style="width: 100%;" onclick="addNewAd();">Добавить объявление</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>

    <? require('../inc/sys/footer.php'); ?>

<script src="http://<?=$_SERVER['HTTP_HOST']?>/js/jquery.min.js"></script>
<script src="http://<?=$_SERVER['HTTP_HOST']?>/js/bootstrap.min.js"></script>
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/autoresize.jquery.min.js"></script>
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/sweet-alert.js"></script>

    <script>
        function addNewAd()
        {
            //Добавляем новое объявление
            var ad_title = $('input[name="title"]').val();
            var ad_description = $('textarea[name="description"]').val();
            var ad_price = $('input[name="price"]').val();

            var ad_id_category = $("#id_category").val();
            var ad_publish_date = $('input[name="public_date"]').val();
            var ad_status = $("#id_status").val();

            var ok_submit = 5;
            var tmp_submit = 0;

            //Валидация
            if(ad_title=='' || ad_title==null)
            {
                $('input[name="title"]').removeClass();
                $('input[name="title"]').addClass('input-error');
            }
            else
            {
                tmp_submit++;
            }

            if(ad_description=='' || ad_description==null)
            {
                $('textarea[name="description"]').removeClass();
                $('textarea[name="description"]').addClass('input-error');
            }
            else
            {
                tmp_submit++;
            }

            if(ad_price=='' || ad_price==null)
            {
                $('input[name="price"]').removeClass();
                $('input[name="price"]').addClass('input-error');
            }
            else
            {
                tmp_submit++;
            }

            if(ad_id_category==0)
            {
                $("#id_category").removeClass();
                $("#id_category").addClass('input-error');
            }
            else
            {
                tmp_submit++;
            }

            if(ad_publish_date=='' || ad_publish_date==null)
            {
                $('input[name="public_date"]').removeClass();
                $('input[name="public_date"]').addClass('input-error');
            }
            else
            {
                tmp_submit++;
            }

            if(ok_submit == tmp_submit)
            {
                //Все ок отправляем
                $.ajax({
                    type: "POST",
                    url: 'work/add_new_ads.php',
                    data: {
                        "ad_title" : ad_title,
                        "ad_description" : ad_description,
                        "ad_price" : ad_price,
                        "ad_id_category" : ad_id_category,
                        "ad_publish_date" : ad_publish_date,
                        "ad_status" : ad_status
                    },
                    beforeSend: function()
                    {
                        $("#loading_add_ad").fadeIn(400);
                    },
                    success: function(resultdata)
                    {
                        $("#loading_add_ad").fadeOut(400);

                        resultauthdata=jQuery.parseJSON(resultdata);

                        if(resultauthdata.status=='ok')
                        {
                            $('input, select, textarea').removeClass();

                            $('input[name="title"]').val('');
                            $('textarea[name="description"]').val('');
                            $('input[name="price"]').val('');


                            swal("Добавлено", "Объявление успешно добавлено. Теперь можно добавить новое объявление.", "success");
                        }
                        else
                        {
                            swal("Ошибка", "Не удалось добавить объявление - ошибка на сервере. Обратитесь к админу", "error");
                        }
                    },
                    error: function()
                    {
                        $("#loading_add_ad").fadeOut(400);
                        swal("Ошибка", "Не удалось добавить объявление. Обратитесь к админу", "error");
                    }
                });
            }

        }

        function firstValidate(e)
        {
            if($(e).val() == '' || $(e).val() == null || $(e).val() == 0)
            {
                $(e).removeClass();
                $(e).addClass('input-error');
            }
            else
            {
                $(e).removeClass();
                $(e).addClass('input-success');
            }
        }


        $(document).ready(function() {
            $('#ta_description').autoResize();
        });
    </script>



</body>
</html>

