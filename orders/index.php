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

//Достаем все категории
$stmt=$db->prepare("SELECT * FROM `specialist_categorys` WHERE 1");
$stmt->execute();

$category_titles = array();

while($category=$stmt->fetch(PDO::FETCH_ASSOC))
{

    $category_titles[$category['id']] = $category['title'];
}


require('../inc/sys/head.php');
?>
<link href="http://<?=$_SERVER['HTTP_HOST']?>/css/sol.css" rel="stylesheet">

</head>

<body>
    <? require('../inc/sys/navigation.php'); ?>

    <div class="container wrapper">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h2 class="h-str">Список заказов
                <button type="button" class="btn btn-info" style="float: right;font-size: 14px;" onclick="$('#search_block').slideToggle(600);">Поиск заказов</button></h2>


            </div>

            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="content-box">

                    <div id="search_block" style="display: none;">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                                <h3 style="margin: 0 0 20px 0; padding: 0 0 0 0;">Поиск заказов</h3>
                            </div>
                        </div>

                        <div class="row">
                            <form name="search_spec" method="get" action="">
                                <input type="hidden" name="onsearch" value="1">
                                <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3">
                                    <p class="text-label">Категория</p>
                                    
                                    <select name="categorys">
                                        <?
                                        $stmt = $db->prepare("SELECT * FROM `specialist_categorys` WHERE 1 ORDER BY  `specialist_categorys`.`title` ASC");
                                        $stmt->execute();

                                        while($specialist_categorys = $stmt->fetch(PDO::FETCH_ASSOC))
                                        {
                                            if($_GET['categorys'] == $specialist_categorys['id'])
                                            {
                                                ?><option value="<?=$specialist_categorys['id']?>" selected><?=$specialist_categorys['title']?></option><?
                                            }
                                            else
                                            {
                                                ?><option value="<?=$specialist_categorys['id']?>"><?=$specialist_categorys['title']?></option><?
                                            }
                                        }
                                        ?>
                                    </select>

                                    <p class="text-label">Дата</p>
                                    <input type="date" name="date" value="<?=$_GET['add_date']?>">

                                    <p class="text-label">Статус</p>
                                    <select name="status">
                                        <option value="Любой" <? if($_GET['status']=='Любой'){echo 'selected';}?>>Любой</option>
                                        <option value="Новый" <? if($_GET['status']=='Новый'){echo 'selected';}?>>Новый</option>
                                        <option value="Приценочный" <? if($_GET['status']=='Приценочный'){echo 'selected';}?>>Приценочный</option>
                                        <option value="Принят в обработку" <? if($_GET['status']=='Принят в обработку'){echo 'selected';}?>>Принят в обработку</option>
                                        <option value="Назначен исполнитель" <? if($_GET['status']=='Назначен исполнитель'){echo 'selected';}?>>Назначен исполнитель</option>
                                        <option value="Осмотр объекта" <? if($_GET['status']=='Осмотр объекта'){echo 'selected';}?>>Осмотр объекта</option>
                                        <option value="Исполняется" <? if($_GET['status']=='Исполняется'){echo 'selected';}?>>Исполняется</option>
                                        <option value="Исполнен" <? if($_GET['status']=='Исполнен'){echo 'selected';}?>>Исполнен</option>
                                        <option value="Закрыт" <? if($_GET['status']=='Закрыт'){echo 'selected';}?>>Закрыт</option>
                                        <option value="Отменен" <? if($_GET['status']=='Отменен'){echo 'selected';}?>>Отменен</option>
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

                    <?
                        if($_GET['onsearch']==1)
                        {
                            ?>
                            <div class="row" style="margin-bottom: 30px;">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <h3 style="margin: 0 0 0 0;">Результат поиска <a href="http://baza-remontprofi.ru/orders/index.php" class="btn btn-default" style="font-size: 14px;float: right;">Сбросить параметры поиска</a></h3>
                                </div>
                            </div>
                            <?
                        }
                    ?>



                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <table class="table table-hover table-bordered table-spec" id="spec_list">
                                <thead>
                                    <tr>
                                        <th style="text-align: center">ID</th>
                                        <th>Дата</th>
                                        <th width=270>Заказчик</th>
                                        <th colspan="2">Исполнитель</th>
                                        <th>Категория заказа</th>
                                        <th>Ориент. сумма</th>
                                        <th>Факт. сумма</th>
                                        <th style="text-align: center;">Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?
                                        $query = 'SELECT `id`, `id_num_day`, `id_call`, `id_manager`, `id_specialist`, `add_date`, `about_object`, `have_materials`, `preparatory`, `price_start`, `price_finish`, `date_view`, `date_work_start`, `date_work_finish`, `address`, `client_name`, `client_phone`, `cpec_categorys`, `prioritet`, `percent`, `finish_price`, `status` FROM `orders` WHERE ';

                                        $have_parametrs = false;
                                        $count_parametrs=0;

                                        if(isset($_GET['categorys']) && $_GET['categorys'][0]!='')
                                        {
                                            $have_parametrs = true;
                                            $count_parametrs++;

                                            if($count_parametrs==1)
                                            {
                                                $query.='(CONCAT(",",cpec_categorys,",") LIKE "%,'.$_GET['categorys'].',%")';
                                            }
                                            else
                                            {
                                                $query.='&& (CONCAT(",",cpec_categorys,",") LIKE "%,'.$_GET['categorys'].',%")';
                                            }
                                        }

                                        if(isset($_GET['date']) && $_GET['date']!='')
                                        {
                                            $have_parametrs = true;
                                            $count_parametrs++;

                                            if($count_parametrs==1)
                                            {
                                                $query.='(date(`add_date`) =  DATE("'.$_GET['date'].'"))';
                                            }
                                            else
                                            {
                                                $query.='&& (date(`add_date`) =  DATE("'.$_GET['date'].'"))';
                                            }
                                        }


                                        if(isset($_GET['status']) && $_GET['status']!='' && $_GET['status']!='Любой')
                                        {
                                            $have_parametrs = true;
                                            $count_parametrs++;


                                            if($count_parametrs==1)
                                            {
                                                $query.='`status` = "'.$_GET['status'].'"';
                                            }
                                            else
                                            {
                                                $query.=' && `status` = "'.$_GET['status'].'"';
                                            }
                                        }


                                        if(!$have_parametrs)
                                        {
                                            $query.=' `status` != "Черный список"';
                                        }

                                        $query.=' ORDER BY  `orders`.`id` DESC';

                                        $stmt = $db->prepare($query);
                                        $stmt->execute();

                                        while($orders = $stmt->fetch(PDO::FETCH_ASSOC))
                                        {
                                            ?>
                                            <tr id="spec_<?=$orders['id']?>">
                                                <td valign="middle" align="center" width="50" style="font-weight: bold; "><?=$orders['id']?></td>

                                                <td class="date" style="border-bottom: 1px solid #ddd; width: 10%;">
                                                    <?=$orders['add_date']?> 
                                                </td>

                                                <td class="client-info" width="200" style="border-bottom: 1px solid #ddd;">
                                                    <a href="http://<?=$_SERVER['HTTP_HOST']?>/orders/view_order.php?id=<?=$orders['id']?>" style="color: #333;">
                                                        <p class="name">
                                                            <span><?=$orders['client_name']?></span>
                                                            <span><?=$orders['client_phone']?></span>
                                                        </p>
                                                    </a>
                                                    
                                                    <p style="font-size: 14px;font-weight: lighter;margin: 2.5px 0 2.5px 0;"><?=$orders['address']?></p>
                                                </td>

                                                <?
                                                    if($orders['id_specialist']==0)
                                                    {
                                                        //Специалист еще не назначен
                                                        if($_GET['spec_selected']==1)
                                                        {
                                                            if($_SESSION['selected_specialist']!='' && $orders['status']=='Принят в обработку')
                                                            {
                                                                $stmt1=$db->prepare("SELECT `id`, `name`, `lastname` FROM `specialist_db` WHERE id=:id_specilaist");
                                                                $stmt1->execute(array("id_specilaist"=>$_SESSION['selected_specialist']));
                                                                $specialist_info=$stmt1->fetch(PDO::FETCH_ASSOC);

                                                                ?>
                                                                <td colspan="2">
                                                                    <a href="http://<?=$_SERVER['HTTP_HOST']?>/orders/view_order.php?id=<?=$orders['id']?>&specialist_selected=<?=$_SESSION['selected_specialist']?>" class="btn btn-warning" style="font-size: 12px;padding: 5px 10px; word-wrap: inherit;">Назначить специалиста<br><?=$specialist_info['name'].' '.$specialist_info['lastname']?></a>
                                                                </td>
                                                                <?
                                                            }
                                                            else
                                                            {
                                                                ?>
                                                                <td colspan="2">
                                                                    <p style="font-style: italic;">Специалист не нужен</p>
                                                                </td>
                                                                <?
                                                            }
                                                        }
                                                        else
                                                        {
                                                            if($orders['status']=='Отменен' || $orders['status']=='Приценочный')
                                                            {
                                                                ?>
                                                                <td colspan="2">
                                                                    <p style="font-style: italic;">Специалист не нужен</p>
                                                                </td>
                                                                <?
                                                            }
                                                            else
                                                            {
                                                                ?>
                                                                <td colspan="2" class="indicator-warrning">
                                                                    <p style="font-style: italic;">Специалист не назначен</p>
                                                                </td>
                                                                <?
                                                            }


                                                        }
                                                    }
                                                    else
                                                    {
                                                        $stmt1=$db->prepare("SELECT `id`, `name`, `lastname`, `avatar_link`, `phone`, `phone2` FROM `specialist_db` WHERE id=:id_specilaist");
                                                        $stmt1->execute(array("id_specilaist"=>$orders['id_specialist']));
                                                        $specialist_info=$stmt1->fetch(PDO::FETCH_ASSOC);

                                                        ?>
                                                        <td class="ava-block" width="66" style="border: none; border-bottom: 1px solid #ddd;">
                                                            <div class="avatar" style="background: url(<? if($specialist_info['avatar_link']!=''){echo $specialist_info['avatar_link'];}else{echo 'http://'.$_SERVER['HTTP_HOST'].'/img/no_photo.jpg';}?>) no-repeat center;" onclick="showProfile(<?=$specialist_info['id']?>);"></div>
                                                        </td>

                                                        <td class="spec-info" width="200" style="border: none; border-bottom: 1px solid #ddd;">
                                                            <p class="name" onclick="showProfile(<?=$specialist_info['id']?>);"><?=$specialist_info['name'].' '.$specialist_info['lastname']?></p>
                                                            <p class="phone"><?=$specialist_info['phone']?></p>
                                                        </td>
                                                        <?
                                                    }
                                                ?>

                                                <td>
                                                    <?
                                                        $ids_categorys = explode(",", $orders['cpec_categorys']);

                                                        $str_categorys = '';

                                                        foreach($ids_categorys as $val)
                                                        {
                                                            $str_categorys.=$category_titles[$val].', ';
                                                        }

                                                        $str_categorys = substr($str_categorys, 0, -2);

                                                        echo $str_categorys;
                                                    ?>
                                                </td>
                                                
                                                <td>
                                                </td>
                                                <td>
                                                </td>
                                                <?
                                                    switch($orders['status'])
                                                    {
                                                        case 'Новый': echo '<td class="td-spec-status os-new">Новый</td>'; break;
                                                        case 'Приценочный': echo '<td class="td-spec-status os-preprice">Приценочный</td>'; break;
                                                        case 'Принят в обработку': echo '<td class="td-spec-status os-process">Принят в обработку</td>'; break;
                                                        case 'Назначен исполнитель': echo '<td class="td-spec-status os-process">Назначен исполнитель</td>'; break;
                                                        case 'Осмотр объекта': echo '<td class="td-spec-status os-process">Осмотр объекта</td>'; break;
                                                        case 'Исполняется': echo '<td class="td-spec-status os-work-process">Исполняется</td>'; break;
                                                        case 'Исполнен': echo '<td class="td-spec-status os-work-process">Исполнен</td>'; break;
                                                        case 'Закрыт': echo '<td class="td-spec-status os-success">Закрыт</td>'; break;
                                                        case 'Отменен': echo '<td class="td-spec-status os-canceled">Отменен</td>'; break;
                                                    }
                                                ?>
                                            </tr>
                                            <?
                                        }
                                        ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <? require('../inc/sys/footer.php'); ?>

    <!-- Окно предпросмотра профиля -->
    <div class="modal fade" id="mini_profile" tabindex="-1" role="dialog" aria-labelledby="spec_name">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="spec_name"></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-3 col-sm-4 col-lg-4">
                            <img id="spec_avatar" src="" class="img-responsive avatar">
                        </div>

                        <div class="col-xs-12 col-sm-9 col-sm-8 col-lg-8">
                            <p class="name"><span id="name"></span><span id="status" class="status"></span></p>
                            <p id="phone1" class="phone"></p>
                            <p id="phone2" class="phone"></p>

                            <p id="categorys" class="categorys"></p>

                            <p class="rating"><i class="icon-starfull"></i><span id="rating"></span></p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-sm-12 col-lg-12">
                            <p style="font-size: 16px; font-weight: bold;">Районы работы</p>
                            <p class="areas" id="areas" style="margin: 0 0 20px 0;"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-sm-12 col-lg-12">
                            <p style="font-size: 16px; font-weight: bold;">Основная информация</p>

                            <table class="table table-hover table-bordered more-info">
                                <tbody>
                                    <tr>
                                        <td class="h" width="50%">Мин. цена</td>
                                        <td width="50%" id="min_price"></td>
                                    </tr>
                                    <tr>
                                        <td class="h">Мин. цена при объеме</td>
                                        <td id="min_price_opt"></td>
                                    </tr>
                                    <tr>
                                        <td class="h">Мин. заказ</td>
                                        <td id="min_price_for_order"></td>
                                    </tr>
                                    <tr>
                                        <td class="h">Опыт работы</td>
                                        <td id="experience"></td>
                                    </tr>
                                    <tr>
                                        <td class="h">Работает по договору</td>
                                        <td id="contract"></td>
                                    </tr>
                                    <tr>
                                        <td class="h">Дает гарантии</td>
                                        <td id="guarantee"></td>
                                    </tr>
                                    <tr>
                                        <td class="h">Увеличение команды</td>
                                        <td id="scale"></td>
                                    </tr>
                                    <tr>
                                        <td class="h">Добавлен в БД</td>
                                        <td id="add_date"></td>
                                    </tr>
                                </tbody>
                            </table>

                            <p style="font-size: 16px; font-weight: bold;">Информация о заказах</p>

                            <table class="table table-hover table-bordered orders-info">
                                <tbody>
                                <tr>
                                    <td class="h" width="50%">Передано контактов</td>
                                    <td width="50%" id="count_spec_add"></td>
                                </tr>
                                <tr>
                                    <td class="h" width="50%">Осмотров</td>
                                    <td width="50%" id="count_view_object"></td>
                                </tr>
                                <tr>
                                    <td class="h" width="50%">Начатых</td>
                                    <td width="50%" id="count_spec_work"></td>
                                </tr>
                                <tr>
                                    <td class="h" width="50%">Исполненных</td>
                                    <td width="50%" id="count_spec_end_work"></td>
                                </tr>
                                <tr>
                                    <td class="h" width="50%">Закрытых</td>
                                    <td width="50%" id="count_close"></td>
                                </tr>
                                <tr>
                                    <td class="h" width="50%">Не оплаченых</td>
                                    <td width="50%" id="count_not_pay"></td>
                                </tr>
                                <tr>
                                    <td class="h" width="50%">Отмененных</td>
                                    <td width="50%" id="count_canceled"></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-sm-12 col-lg-12 comment">
                            <p style="font-size: 16px; font-weight: bold;">Комментарий</p>

                            <div id="comment"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-sm-12 col-lg-12">
                            <p style="font-size: 16px; font-weight: bold;">10 последных событий</p>

                            <div id="events_list" class="events-list"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" style="font-size: 14px;" data-dismiss="modal">Закрыть</button>

                    <div class="btn-group dropup" style="min-width: 320px">
                        <button type="button" class="btn btn-warning dropdown-toggle" style="font-size: 14px; width: 100%;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Действия<span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" style="width: 100%;">
                            <li><a href="#">Посмотреть полный профиль</a></li>
                            <li><a href="#">Добавить событие обратной связи</a></li>
                            <li><a href="#">Назначить исполнителем для заказа</a></li>

                            <li><a id="li_up_status">Сменить статус</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="update_status" tabindex="-1" role="dialog" aria-labelledby="spec_status">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Изменить статус специалиста <span id="us_spec_name"></span></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <p class="text-label">Выберите статус</p>

                            <select id="new_stat_sp" name="new_status_spec">
                                <option value="Свободен">Свободен</option>
                                <option value="Занят. Н">Занят. Н</option>
                                <option value="Занят. Д">Занят. Д</option>
                                <option value="На осмотре">На осмотре</option>
                                <option value="Выходной">Выходной</option>
                                <option value="Под подозрением">Под подозрением</option>
                                <option value="Черный список">Черный список</option>
                            </select>

                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 20px;">
                                    
                                    <div id="loading_status" class="loading" title="Статус обновлен"></div>
                                    
                                    <p id="result_update_status" style="font-size: 16px; color: #f00; font-weight: bold; display: none;">Статус обновлен</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                                    <button type="button" class="btn btn-success" style="font-size: 14px;" id="btn_up_status">Обновить статус</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" style="font-size: 14px;" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/jquery.min.js"></script>
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/bootstrap.min.js"></script>
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/sol.js"></script>
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/sweet-alert.js"></script>
<!--    <script src="http://--><?//=$_SERVER['HTTP_HOST']?><!--/js/jquery.tablesorter.js"></script>-->


    <script>
        function showProfile(id, type_show = null)
        {
            //Получаем информацию о специалисте
            $.ajax({
                type: "POST",
                url: 'work/get_specialist_info.php',
                data: {"id_specialist" : id},
                success: function(resultdata)
                {
                    resultauthdata=jQuery.parseJSON(resultdata);

                    if(resultauthdata.status_sel=='ok')
                    {
                        $('#mini_profile #spec_name').text(resultauthdata.name_lastname);

                        $('#mini_profile #spec_avatar').attr("src", resultauthdata.avatar_link);

                        $('#mini_profile #name').text(resultauthdata.name_lastname);
                        $('#mini_profile #phone1').text(resultauthdata.phone);
                        $('#mini_profile #phone2').text(resultauthdata.phone2);

                        $('#mini_profile #categorys').text(resultauthdata.categorys);

                        $('#mini_profile #rating').text(resultauthdata.rating);

                        $('#mini_profile #areas').text(resultauthdata.areas);

                        $('#mini_profile #status').removeClass();

                        switch(resultauthdata.status)
                        {
                            case 'Свободен':
                                $('#mini_profile #status').text(resultauthdata.status);
                                $('#mini_profile #status').addClass("status tss-free");
                                break;
                            case 'Занят. Н':
                                $('#mini_profile #status').text(resultauthdata.status);
                                $('#mini_profile #status').addClass("status tss-busy");
                                break;
                            case 'Занят. Д':
                                $('#mini_profile #status').text(resultauthdata.status);
                                $('#mini_profile #status').addClass("status tss-busy");
                                break;
                            case 'На осмотре':
                                $('#mini_profile #status').text(resultauthdata.status);
                                $('#mini_profile #status').addClass("status tss-view");
                                break;
                            case 'Выходной':
                                $('#mini_profile #status').text(resultauthdata.status);
                                $('#mini_profile #status').addClass("status tss-output");
                                break;
                            case 'Под подозрением':
                                $('#mini_profile #status').text(resultauthdata.status);
                                $('#mini_profile #status').addClass("status tss-suss");
                                break;
                            case 'Черный список':
                                $('#mini_profile #status').text(resultauthdata.status);
                                $('#mini_profile #status').addClass("status tss-bl");
                                break;
                        }

                        //Инфа о заказах
                        $('#mini_profile #count_spec_add').text(resultauthdata.count_spec_add);
                        $('#mini_profile #count_view_object').text(resultauthdata.count_view_object);
                        $('#mini_profile #count_spec_work').text(resultauthdata.count_spec_work);
                        $('#mini_profile #count_spec_end_work').text(resultauthdata.count_spec_end_work);
                        $('#mini_profile #count_close').text(resultauthdata.count_close);
                        $('#mini_profile #count_not_pay').text(resultauthdata.count_not_pay);
                        $('#mini_profile #count_canceled').text(resultauthdata.count_canceled);

                        //Основная инфа
                        $('#mini_profile #min_price').text(resultauthdata.min_price);
                        $('#mini_profile #min_price_opt').text(resultauthdata.min_price_opt);
                        $('#mini_profile #min_price_for_order').text(resultauthdata.min_price_for_order);
                        $('#mini_profile #experience').text(resultauthdata.experience);
                        $('#mini_profile #contract').text(resultauthdata.contract);
                        $('#mini_profile #guarantee').text(resultauthdata.guarantee);
                        $('#mini_profile #scale').text(resultauthdata.scale);
                        $('#mini_profile #add_date').text(resultauthdata.add_date);

                        //Коммент
                        $('#mini_profile #comment').html(resultauthdata.comment);

                        //Последнии события
                        $('#events_list').html('');

                        var new_content = '';

                        $.each(resultauthdata.last_events, function(index, value)
                        {
                            new_content+='<div class="event"><p class="date" id="e_add_date">'+value.add_date+'</p><div class="text" id="e_comment">'+value.comment+'</div></div>';
                        });

                        if(new_content=='')
                        {
                            new_content = 'Событий нет';
                        }

                        $('#events_list').html(new_content);

                        //Параметры доп функций
                        $('#li_up_status').attr('onclick', 'showUpdateStatus('+resultauthdata.id+', "'+resultauthdata.name_lastname+'")');

                        if(type_show=='handleUpdate')
                        {
                            alert(1);
                            $('#mini_profile').modal('show');
                            $('#mini_profile').focus()
                        }
                        else
                        {
                            alert(0);
                            $('#mini_profile').modal('show');
                        }
                    }
                    else
                    {
                        swal("Ошибка", "Не удалось загрузить информацию о специалисте!", "error");
                    }
                },
                error: function()
                {
                    swal("Ошибка", "Не удалось загрузить информацию о специалисте!", "error");
                }
            });
        }

        function updateStatus(id)
        {
            var new_stat = $("#new_stat_sp").val();

            $.ajax({
                    type: "POST",
                    url: 'work/update_specialist.php',
                    data: { "status": new_stat, "id_specialist" :  id},
                    beforeSend: function()
                    {
                        $("#loading_status").fadeIn(400);
                    },
                    success: function(resultdata)
                    {
                        $("#loading_status").fadeOut(400);

                        resultauthdata=jQuery.parseJSON(resultdata);

                        if(resultauthdata.status=='ok')
                        {
                            $('#result_update_status').fadeIn(400);

                            setTimeout(function(){
                                $('#update_status').modal('hide');

                                showProfile(id, 'handleUpdate');
                                
                            }, 3000)
                            
                        }
                        else
                        {
                            swal("Ошибка", "Не удалось обновить статус - ошибка на сервере. Обратитесь к админу", "error");
                        }
                    },
                    error: function()
                    {
                        $("#loading_update").fadeOut(400);
                        swal("Ошибка", "Не удалось обновить статус. Обратитесь к админу", "error");
                    }
                });
        }

        function showUpdateStatus(id, name_lastname)
        {
            $('#update_status #us_spec_name').text(name_lastname);

            $('#btn_up_status').attr('onclick', 'updateStatus('+id+')');

            $('#mini_profile').modal('hide');

            $('#update_status').modal('show');
        }

        $(function () {
            //Категории
            $('#select_categorys').searchableOptionList({
                useBracketParameters: true
            });
        });

        $(document).ready(function() {
//            $("#spec_list").tablesorter();
        });
    </script>

</body>
</html>

