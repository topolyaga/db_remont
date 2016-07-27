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
</head>

<body>
    <? require('../inc/sys/navigation.php'); ?>

    <div class="container wrapper">
        <div class="row">
            <h2 class="h-str">Объявления для Авито
                <button type="button" class="btn btn-info" style="float: right;font-size: 14px;" onclick="$('#search_block').slideToggle(600);">Параметры поиска</button></h2>

            <div class="content-box col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div id="search_block" style="display: none;">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                            <h3 style="margin: 0 0 20px 0; padding: 0 0 0 0;">Поиск объявлений</h3>
                        </div>
                    </div>

                    <div class="row">
                        <form name="new_ads" action="" method="get">
                            <input type="hidden" name="onsearch" value="1">

                            <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3">
                                <p class="text-label">Выберите категорию</p>
                                <select name="category" required>
                                    <?
                                    
                                    $categorys_name_array = array();

                                    $stmt = $db->prepare("SELECT * FROM `ads_categorys` WHERE 1");
                                    $stmt->execute();

                                    while($ads_categorys = $stmt->fetch(PDO::FETCH_ASSOC))
                                    {
                                        $categorys_name_array[$ads_categorys['id']] = $ads_categorys['title'];

                                        if($ads_categorys['id']==$_GET['category'])
                                        {
                                            ?><option value="<?=$ads_categorys['id']?>" selected><?=$ads_categorys['title']?></option><?
                                        }
                                        else
                                        {
                                            ?><option value="<?=$ads_categorys['id']?>"><?=$ads_categorys['title']?></option><?
                                        }
                                    }
                                    ?>
                                </select>

                                <p class="text-label">Выберите дату</p>
                                <input type="date" name="public_date" value="<?=$_GET['public_date']?>">

                                <p class="text-label">Статус</p>
                                <select id="id_status" name="status" required>
                                    <option value="Черновик">Черновик</option>
                                    <option value="Готово к публикации" selected>Готово к публикации</option>
                                </select>

                                <div class="row" style="margin-top: 30px;">
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                                        <button type="submit" class="btn btn-success">Поиск</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                <hr>

                </div>

                <table class="table table-hover table-bordered">
                    <thead>
                    <tr>
                        <th style="text-align: center">ID</th>
                        <th>Загаловок</th>
                        <th>Текст</th>
                        <th>Цена</th>
                        <th>Категория</th>
                        <th>Дата публикации</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                        <?
			//status='Готово к публикации'
				
                        $stmt = $db->prepare("SELECT * FROM `ads` WHERE 1");
                        $stmt->execute();

                        while($ads = $stmt->fetch(PDO::FETCH_ASSOC))
                        {
                            ?>
                            <tr id="ad_<?=$ads['id']?>">
                                <td><?=$ads['id']?></td>
                                <td>
                                    <?=$ads['title']?>
                                    <input type="hidden" id="ad_<?=$ads['id']?>_title" value="<?=$ads['title']?>">
                                </td>
                                <td><?=nl2br($ads['description'])?></td>
                                <td><?=$ads['price']?></td>
                                <td><?=$categorys_name_array[$ads['id_categorys']]?></td>
                                <td><?=$ads['public_date']?></td>
                                <td><?=$ads['status']?></td>
                                <td id="btn_ups_ad_<?=$ads['id']?>"><button class="btn btn-warning" style="font-size: 14px;" onclick="okPublish(<?=$ads['id']?>, 'btn_ups_ad_<?=$ads['id']?>');">Опубликовал</button></td>
                            </tr>
                            <?
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <? require('../inc/sys/footer.php'); ?>

    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/jquery.min.js"></script>
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/bootstrap.min.js"></script>
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/clipboard.min.js"></script>

    <script>
        function okPublish(id_ad, id_tr)
        {
            $.ajax({
                type: "POST",
                url: 'work/update_ad_status.php',
                data: {
                    "id_ad" : id_ad,
                    "status" : "publish"
                },
                success: function(resultdata)
                {
                    resultauthdata=jQuery.parseJSON(resultdata);

                    if(resultauthdata.status=='ok')
                    {
                        $('#'+id_tr).html('Опубликовано');
                    }
                    else
                    {
                        swal("Ошибка", "Не удалось обновить статус - ошибка на сервере. Обратитесь к админу", "error");
                    }
                },
                error: function()
                {
                    swal("Ошибка", "Не удалось обновить статус. Обратитесь к админу", "error");
                }
            });
        }

    </script>

</body>
</html>

