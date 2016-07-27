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

$add_spec_for_order = false;

if($_GET['add_spec_for_order']==1)
{
    $add_spec_for_order = true;
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
                <h2 class="h-str">Список специалистов
                    <button type="button" class="btn btn-info" style="float: right;font-size: 14px;" onclick="$('#search_block').slideToggle(600);">Поиск специалистов</button></h2>


            </div>

            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="content-box">

                    <div id="search_block" style="display: none;">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                                <h3 style="margin: 0 0 20px 0; padding: 0 0 0 0;">Поиск специалистов</h3>
                            </div>
                        </div>

                        <div class="row">
                            <form name="search_spec" method="get" action="">
                                <input type="hidden" name="onsearch" value="1">
                                <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3">
                                    <p class="text-label">Категория</p>
                                    <select id="select_categorys" name="categorys" multiple>
                                        <?
                                        $stmt = $db->prepare("SELECT * FROM `specialist_categorys` WHERE 1 ORDER BY  `specialist_categorys`.`title` ASC");
                                        $stmt->execute();

                                        while($specialist_categorys = $stmt->fetch(PDO::FETCH_ASSOC))
                                        {
                                            if(isset($_GET['categorys']))
                                            {
                                                $has = false;

                                                foreach ($_GET['categorys'] as $val)
                                                {
                                                    if($val == $specialist_categorys['id'])
                                                    {
                                                        $has = true;

                                                        ?><option value="<?=$specialist_categorys['id']?>" selected><?=$specialist_categorys['title']?></option><?
                                                    }
                                                }

                                                if(!$has)
                                                {
                                                    ?><option value="<?=$specialist_categorys['id']?>"><?=$specialist_categorys['title']?></option><?
                                                }
                                            }
                                            else
                                            {
                                                ?><option value="<?=$specialist_categorys['id']?>"><?=$specialist_categorys['title']?></option><?
                                            }
                                        }
                                        ?>
                                    </select>

                                    <p class="text-label">Принцип выбора категории</p>
                                    <select name="type_category">
                                        <option value="i" <? if($_GET['type_category']=='i'){echo 'selected';}?>>И то И то</option>
                                        <option value="ili" <? if($_GET['type_category']=='ili'){echo 'selected';}?>>ИЛИ то ИЛИ то</option>
                                    </select>

                                    <p class="text-label">Район</p>
                                    <input type="text" name="areas" value="<?=$_GET['areas']?>">

                                    <p class="text-label">Мин. цена</p>

                                    <div class="row">
                                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                                            <input type="number" name="min_price" value="<?=$_GET['min_price']?>">
                                        </div>
                                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                                            <select name="type_price">
                                                <option value="roz" <? if($_GET['type_price']=='roz'){echo 'selected';}?>>Розница</option>
                                                <option value="opt" <? if($_GET['type_price']=='opt'){echo 'selected';}?>>Опт</option>
                                            </select>
                                        </div>
                                    </div>

                                    <p class="text-label">Мин. заказ</p>
                                    <input type="number" name="min_price_order" value="<?=$_GET['min_price_order']?>">

                                    <p class="text-label">Статус</p>
                                    <select name="status">
                                        <option value="Любойкчс" <? if($_GET['status']=='Любойкчс'){echo 'selected';}?>>Любой, кроме Черного списка</option>
                                        <option value="Свободен" <? if($_GET['status']=='Свободен'){echo 'selected';}?>>Свободен</option>
                                        <option value="Занят. Н" <? if($_GET['status']=='Занят. Н'){echo 'selected';}?>>Занят. Н</option>
                                        <option value="Занят. Д" <? if($_GET['status']=='Занят. Д'){echo 'selected';}?>>Занят. Д</option>
                                        <option value="На осмотре" <? if($_GET['status']=='На осмотре'){echo 'selected';}?>>На осмотре</option>
                                        <option value="Выходной" <? if($_GET['status']=='Выходной'){echo 'selected';}?>>Выходной</option>
                                        <option value="Под подозрением" <? if($_GET['status']=='Под подозрением'){echo 'selected';}?>>Под подозрением</option>
                                        <option value="Черный список" <? if($_GET['status']=='Черный список'){echo 'selected';}?>>Черный список</option>
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
                                    <h3 style="margin: 0 0 0 0;">Результат поиска <a href="http://baza-remontprofi.ru/specialisti/list.php" class="btn btn-default" style="font-size: 14px;float: right;">Сбросить параметры поиска</a></h3>
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
                                        <th colspan="2">Специалист</th>
                                        <th>Категория</th>
                                        <th>Приоритетные районы</th>
                                        <th style="text-align: center">Мин. цена<br>(роз/опт)</th>
                                        <th style="text-align: center">Мин. заказ</th>
                                        <th style="text-align: center" width="90">Рейтинг</th>
                                        <th style="text-align: center">Статус</th>
                                        <?
                                        if($add_spec_for_order)
                                        {
                                            ?>
                                                <th style="text-align: center;">На заказ #<?=$_GET['id_order']?></th>
                                            <?
                                        }
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?
                                        $query = 'SELECT `id`, `name`, `lastname`, `avatar_link`, `id_categorys`, `phone`, `min_price`, `min_price_opt`, `min_price_for_order`, `areas`, `status` FROM `specialist_db` WHERE ';

                                        $have_parametrs = false;
                                        $count_parametrs=0;

                                        if(isset($_GET['categorys']) && $_GET['categorys'][0]!='')
                                        {
                                            $have_parametrs = true;
                                            $count_parametrs++;

                                            $dop_str = '(';

                                            //узнаем кол-во выбранных категорий

                                            $cat_count = 0;

                                            foreach($_GET['categorys'] as $val)
                                            {
                                                $cat_count++;
                                            }

                                            $i=0;

                                            switch($_GET['type_category'])
                                            {
                                                case 'i': $type_category_select = '&&'; break;
                                                case 'ili': $type_category_select = '||'; break;
                                            }

                                            foreach ($_GET['categorys'] as $val)
                                            {
                                                $i++;

                                                if($cat_count==1)
                                                {
                                                    $dop_str.='CONCAT(",",id_categorys,",") LIKE "%,'.$val.',%"';
                                                }

                                                if($i<$cat_count && $cat_count!=1)
                                                {
                                                    $dop_str.='CONCAT(",",id_categorys,",") LIKE "%,'.$val.',%" '.$type_category_select.' ';
                                                }

                                                if($i==$cat_count && $cat_count!=1)
                                                {
                                                    $dop_str.='CONCAT(",",id_categorys,",") LIKE "%,'.$val.',%"';
                                                }

                                            }

                                            $dop_str.=') ';

                                            $query.=$dop_str;
                                        }

                                        if(isset($_GET['areas']) && $_GET['areas']!='')
                                        {
                                            $have_parametrs = true;
                                            $count_parametrs++;

                                            if($count_parametrs==1)
                                            {
                                                $query.='(`areas` LIKE "%'.$_GET['areas'].'%")';
                                            }
                                            else
                                            {
                                                $query.=' && (`areas` LIKE "%'.$_GET['areas'].'%")';
                                            }
                                        }

                                        if(isset($_GET['min_price']) && $_GET['min_price']!='')
                                        {
                                            $have_parametrs = true;
                                            $count_parametrs++;

                                            switch($_GET['type_price'])
                                            {
                                                case 'roz': $type_price = 'min_price'; break;
                                                case 'opt': $type_price = 'min_price_opt'; break;
                                            }

                                            if($count_parametrs==1)
                                            {
                                                $query.='(`'.$type_price.'` <= '.$_GET['min_price'].')';
                                            }
                                            else
                                            {
                                                $query.=' && (`'.$type_price.'` <= '.$_GET['min_price'].')';
                                            }
                                        }

                                        if(isset($_GET['min_price_order']) && $_GET['min_price_order']!='')
                                        {
                                            $have_parametrs = true;
                                            $count_parametrs++;

                                            if($count_parametrs==1)
                                            {
                                                $query.='(`min_price_for_order` >= '.$_GET['min_price_order'].')';
                                            }
                                            else
                                            {
                                                $query.=' && (`min_price_for_order` >= '.$_GET['min_price_order'].')';
                                            }
                                        }

                                        if(isset($_GET['status']) && $_GET['status']!='' && $_GET['status']!='Любойкчс')
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


                                        $stmt = $db->prepare($query);
                                        $stmt->execute();

                                        while($specialisti = $stmt->fetch(PDO::FETCH_ASSOC))
                                        {

                                            ?>
                                            <tr id="spec_<?=$specialisti['id']?>">
                                                <td valign="middle" align="center" width="50" style="font-weight: bold; "><?=$specialisti['id']?></td>

                                                <td class="ava-block" width="66" style="border: none; border-bottom: 1px solid #ddd;">
                                                    <div class="avatar" style="background: url(<? if($specialisti['avatar_link']!=''){echo $specialisti['avatar_link'];}else{echo 'http://'.$_SERVER['HTTP_HOST'].'/img/no_photo.jpg';}?>) no-repeat center;" onclick="showProfile(<?=$specialisti['id']?>);"></div>
                                                </td>

                                                <td class="spec-info" width="200" style="border: none; border-bottom: 1px solid #ddd;">
                                                    <p class="name" onclick="showProfile(<?=$specialisti['id']?>);"><?=$specialisti['name'].' '.$specialisti['lastname']?></p>
                                                    <p class="phone"><?=$specialisti['phone']?>
                                                </td>



                                                <td style="vertical-align: top !important; max-width: 180px;">
                                                    <?
                                                        $ids_categorys = explode(",", $specialisti['id_categorys']);

                                                        $str_categorys = '';

                                                        foreach($ids_categorys as $val)
                                                        {
                                                            $str_categorys.=$category_titles[$val].', ';
                                                        }

                                                        $str_categorys = substr($str_categorys, 0, -2);

                                                        echo $str_categorys;
                                                    ?>
                                                </td>
                                                <td style="vertical-align: top !important; max-width: 180px;"><?=$specialisti['areas']?></td>
                                                <td style="font-weight: bold; color: #5cb85c; text-align: center;" width="120"><?=number_format($specialisti['min_price'], 0, '.', ' ')?> / <?=number_format($specialisti['min_price_opt'], 0, '.', ' ').' руб'?></td>
                                                <td style="font-weight: bold; color: #f00; text-align: center;" width="110"><?=number_format($specialisti['min_price_for_order'], 0, '.', ' ').' руб'?></td>
                                                <td style="font-weight: bold; text-align: center; font-size: 18px;">
                                                    <?
                                                        $stmt1=$db->prepare("SELECT SUM(value)/count(id) as rating FROM `specialist_rating` WHERE id_specialist=:id_specialist");
                                                        $stmt1->execute(array("id_specialist"=>$specialisti['id']));
                                                        $rating=$stmt1->fetch(PDO::FETCH_ASSOC);

                                                        echo round($rating['rating'], 2);
                                                    ?>
                                                </td>
                                                <?
                                                    switch($specialisti['status'])
                                                    {
                                                        case 'Свободен': echo '<td id="status_'.$specialisti['id'].'" class="td-spec-status tss-free">Свободен</td>'; break;
                                                        case 'Занят. Н': echo '<td id="status_'.$specialisti['id'].'" class="td-spec-status tss-busy">Занят. Н</td>'; break;
                                                        case 'Занят. Д': echo '<td id="status_'.$specialisti['id'].'" class="td-spec-status tss-busy">Занят. Д</td>'; break;
                                                        case 'На осмотре': echo '<td id="status_'.$specialisti['id'].'" class="td-spec-status tss-view">На осмотре</td>'; break;
                                                        case 'Выходной': echo '<td id="status_'.$specialisti['id'].'" class="td-spec-status tss-output">Выходной</td>'; break;
                                                        case 'Под подозрением': echo '<td id="status_'.$specialisti['id'].'" class="td-spec-status tss-suss">Под подозрением</td>'; break;
                                                        case 'Черный список': echo '<td id="status_'.$specialisti['id'].'" class="td-spec-status tss-bl">Черный список</td>'; break;
                                                    }
                                                ?>

                                                <?
                                                if($add_spec_for_order)
                                                {
                                                    ?>
                                                    <td>
                                                        <a href="http://<?=$_SERVER['HTTP_HOST']?>/orders/view_order.php?id=<?=$_GET['id_order']?>&add_spec_from_list=1&id_specialist=<?=$specialisti['id']?>" class="btn btn-warning" style="font-size: 12px;">Назначить</a>
                                                    </td>
                                                    <?
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
                            <li><a id="li_profile_link" target="_blank">Посмотреть полный профиль</a></li>
                            <li><a id="li_add_event">Добавить событие</a></li>
                            <li><a id="li_add_specialist_selected">Назначить исполнителем для заказа</a></li>

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
                            <select id="new_stat_sp" name="new_status_spec" style="margin: 0 0 0 0;">
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

    <div class="modal fade" id="add_event" tabindex="-1" role="dialog" aria-labelledby="spec_event">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Добавление события для специалиста <span id="aec_spec_name"></span></h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

                            <p class="text-label">Выберите тип события</p>

                            <select id="id_event_type" name="event_type" style="margin: 0 0 0 0;" onchange="showDopInfoForEvent();">
                                <option>Выбрать</option>
                                <optgroup label="Обратная связь">
<!--                                    <option value="callback-order">По заказу</option>-->
                                    <option value="callback-communication">По выходу на связь</option>
                                    <option value="callback-org_info">По организационным вопросам</option>
                                </optgroup>

                                <!--<optgroup label="Заказ (задается автоматически только в заказе)" disabled>
                                    <option value="order-send_contacts">Передача контактных данных</option>
                                    <option value="order-view">Осмотр объекта</option>
                                    <option value="order-adoption">Прием заказа (назначение)</option>
                                    <option value="order-renouncement">Отказ от заказа</option>
                                    <option value="order-cut">Снятие с заказа</option>
                                    <option disabled>Процесс исполнения</option>
                                    <option value="order-process_comment">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Комментарий</option>
                                    <option value="order-process_correction">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Корректировка</option>
                                    <option value="order-process_break">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Технический перерыв</option>
                                    <option value="order-process_claim">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Претензия</option>
                                    <option value="order-complite">Исполнение заказа (окончание работ)</option>
                                    <option value="order-close">Закрытие заказа</option>
                                </optgroup>-->

                                <optgroup label="Взаимодействие с компанией">
<!--                                    <option value="company-pay" disabled>Выплата комиссии (задается автоматически только в заказе)</option>-->
                                    <option value="company-long_time_pay">Задержка выплаты комиссии</option>
                                    <option value="company-no_pay">Невыплата комиссии</option>
                                </optgroup>

                                <optgroup label="Другое">
                                    <option value="comment">Комментарий</option>
                                </optgroup>
                            </select>

                            <div class="row">
                                <div id="dop_info_for_event" class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="margin-top: 20px;">
                                    <div id="callback_communication" style="display: none;">
                                        <p style="text-align: center; font-size: 18px; font-weight: lighter; margin: 0 0 20px 0;">Укажите дополнительную информацию</p>

                                        <p class="text-label">Комментарий</p>
                                        <textarea name="comment_for_callback_communication" style="width: 100%; height: 70px; font-size: 16px;" placeholder="Введите комментарий к событию"></textarea>

                                        <p class="text-label">Оценка (идет в рейтиг)</p>
                                        <select id="rating_for_callback_communication">
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4" selected>4</option>
                                            <option value="5">5</option>
                                        </select>
                                    </div>

                                    <div id="callback_org_info" style="display: none;">
                                        <p style="text-align: center; font-size: 18px; font-weight: lighter; margin: 0 0 20px 0;">Укажите дополнительную информацию</p>

                                        <p class="text-label">Комментарий</p>
                                        <textarea name="comment_for_callback_org_info" style="width: 100%; height: 70px; font-size: 16px;" placeholder="Введите комментарий к событию"></textarea>
                                    </div>

                                    <div id="company_long_time_pay" style="display: none;">
                                        <p style="text-align: center; font-size: 18px; font-weight: lighter; margin: 0 0 20px 0;">Укажите дополнительную информацию</p>

                                        <p class="text-label">ID заказа</p>
                                        <input type="number" name="id_order_for_company_long_time_pay" placeholder="ID заказа">

                                        <p class="text-label">Комментарий</p>
                                        <textarea name="comment_for_company_long_time_pay" style="width: 100%; height: 70px; font-size: 16px;" placeholder="Введите комментарий к событию"></textarea>

                                            <option value="1">1</option>
                                        <p class="text-label">Оценка (идет в рейтиг)</p>
                                        <select id="rating_for_company_long_time_pay">
                                            <option value="2">2</option>
                                            <option value="3" selected>3</option>
                                        </select>
                                    </div>

                                    <div id="company_no_pay" style="display: none;">
                                        <p style="text-align: center; font-size: 18px; font-weight: lighter; margin: 0 0 20px 0;">Укажите дополнительную информацию</p>

                                        <p class="text-label">ID заказа</p>
                                        <input type="number" name="id_order_for_company_no_pay" placeholder="ID заказа">

                                        <p class="text-label">Комментарий</p>
                                        <textarea name="comment_for_company_no_pay" style="width: 100%; height: 70px; font-size: 16px;" placeholder="Введите комментарий к событию"></textarea>

                                        <p class="text-label">Оценка (идет в рейтиг)</p>
                                        <select id="rating_for_company_no_pay">
                                            <option value="1" selected>1</option>
                                        </select>
                                    </div>

                                    <div id="comment" style="display: none;">
                                        <p style="text-align: center; font-size: 18px; font-weight: lighter; margin: 0 0 20px 0;">Укажите дополнительную информацию</p>

                                        <p class="text-label">Комментарий</p>
                                        <textarea name="comment" style="width: 100%; height: 70px; font-size: 16px;" placeholder="Введите комментарий к событию"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 20px;">

                                    <div id="loading_event" class="loading"></div>

                                    <p id="result_add_event" style="font-size: 16px; color: #f00; font-weight: bold; display: none;">Событие добавленно</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 20px;">

                                    <div id="loading_add_event" class="loading"></div>

                                    <p id="result_add_event" style="font-size: 16px; color: #f00; font-weight: bold; display: none;">Событие добавлено</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                                    <button type="button" class="btn btn-success" style="font-size: 14px;" id="btn_add_event">Добавить событие</button>
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
        function showProfile(id)
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
                        $('#mini_profile #spec_name').text(resultauthdata.id_name_lastname);

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

                        $('#li_profile_link').attr('href', 'http://baza-remontprofi.ru/specialisti/view_profile.php?id='+resultauthdata.id);

                        //Параметры доп функций
                        $('#li_up_status').attr('onclick', 'showUpdateStatus('+resultauthdata.id+', "'+resultauthdata.name_lastname+'")');
                        $('#li_add_event').attr('onclick', 'showEvent('+resultauthdata.id+', "'+resultauthdata.name_lastname+'")');
                        $('#li_add_specialist_selected').attr('onclick', 'addSpecialistForOrder('+resultauthdata.id+')');

                        $('#mini_profile').modal('show');
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
                    data: {"id_specialist" : id, "type" : "status", "status": new_stat},
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

                                $('#update_status').on('hidden.bs.modal', function() {

                                    $('#result_update_status').hide(0);

                                    //Обновим статус в таблице
                                    switch(new_stat)
                                    {
                                        case 'Свободен':
                                            $('#status_'+id).removeClass();
                                            $('#status_'+id).addClass('td-spec-status tss-free');
                                            $('#status_'+id).text('Свободен');
                                            break;

                                        case 'Занят. Н':
                                            $('#status_'+id).removeClass();
                                            $('#status_'+id).addClass('td-spec-status tss-busy');
                                            $('#status_'+id).text('Занят. Н');
                                            break;

                                        case 'Занят. Д':
                                            $('#status_'+id).removeClass();
                                            $('#status_'+id).addClass('td-spec-status tss-busy');
                                            $('#status_'+id).text('Занят. Д');
                                            break;

                                        case 'На осмотре':
                                            $('#status_'+id).removeClass();
                                            $('#status_'+id).addClass('td-spec-status tss-view');
                                            $('#status_'+id).text('На осмотре');
                                            break;

                                        case 'Выходной':
                                            $('#status_'+id).removeClass();
                                            $('#status_'+id).addClass('td-spec-status tss-output');
                                            $('#status_'+id).text('Выходной');
                                            break;

                                        case 'Под подозрением':
                                            $('#status_'+id).removeClass();
                                            $('#status_'+id).addClass('td-spec-status tss-output');
                                            $('#status_'+id).text('Под подозрением');
                                            break;

                                        case 'Черный список':
                                            $('#status_'+id).removeClass();
                                            $('#status_'+id).addClass('td-spec-status tss-bl');
                                            $('#status_'+id).text('Черный список');
                                            break;
                                    }

                                    showProfile(id);
                                });
                            }, 1000);

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

        function addSpecialistForOrder(id)
        {
            //Добавление спеца к заказу

            $.ajax({
                type: "POST",
                url: 'work/add_specialist_for_selected.php',
                data: {"id_specialist" : id},
                success: function(resultdata)
                {
                    resultauthdata=jQuery.parseJSON(resultdata);

                    if(resultauthdata.status=='ok')
                    {
                        //Все ок спец в выбраных, теперь переходим к заказам
                        window.location.href = 'http://baza-remontprofi.ru/orders/index.php?spec_selected=1&status=Принят+в+обработку';
                    }
                    else
                    {
                        swal("Ошибка", "Не удалось добавить специалиста к выбранным - ошибка на сервере. Обратитесь к админу", "error");
                    }
                },
                error: function()
                {

                    swal("Ошибка", "Не удалось добавить специалиста к выбранным. Обратитесь к админу", "error");
                }
            });
        }

        function addEventForSpecialist(id)
        {
            var event_type = $('#id_event_type').val();

            var ok_submit = false;

            switch (event_type)
            {
                case 'callback-communication':
                    var comment = $('textarea[name="comment_for_callback_communication"]').val();
                    var rating = $('#rating_for_callback_communication').val();

                    ok_submit = true;
                    break;
                case 'callback-org_info':
                    var comment = $('textarea[name="comment_for_callback_org_info"]').val();

                    ok_submit = true;
                    break;
                case 'company-long_time_pay':
                    var id_order = $('input[name="id_order_for_company_long_time_pay"]').val();
                    var comment = $('textarea[name="comment_for_company_long_time_pay"]').val();
                    var rating = $('#rating_for_company_long_time_pay').val();

                    ok_submit = true;
                    break;
                case 'company-no_pay':
                    var id_order = $('input[name="id_order_for_company_no_pay"]').val();
                    var comment = $('textarea[name="comment_for_company_no_pay"]').val();
                    var rating = $('#rating_for_company_no_pay').val();

                    ok_submit = true;
                    break;
                case 'comment':
                    var comment = $('textarea[name="comment"]').val();

                    ok_submit = true;
                    break;
            }

            if(rating == null || rating == 0)
            {
                rating = 'none';
            }

            if(event_type == 'company-long_time_pay')
            {
                if(id_order == null || id_order == 0)
                {
                    $('input[name="id_order_for_company_long_time_pay"]').removeClass();
                    $('input[name="id_order_for_company_long_time_pay"]').addClass('input-error');

                    ok_submit = false;
                }
                else
                {
                    $('input[name="id_order_for_company_long_time_pay"]').removeClass();
                    $('input[name="id_order_for_company_long_time_pay"]').addClass('input-success');

                    ok_submit = true;
                }
            }

            if(event_type == 'company-no_pay')
            {
                if(id_order == null || id_order == 0)
                {
                    $('input[name="id_order_for_company_no_pay"]').removeClass();
                    $('input[name="id_order_for_company_no_pay"]').addClass('input-error');

                    ok_submit = false;
                }
                else
                {
                    $('input[name="id_order_for_company_no_pay"]').removeClass();
                    $('input[name="id_order_for_company_no_pay"]').addClass('input-success');

                    ok_submit = true;
                }
            }

            if(ok_submit) {
                $.ajax({
                    type: "POST",
                    url: 'work/update_specialist.php',
                    data: {
                        "id_specialist": id,
                        "type": "event",
                        "event_type": event_type,
                        "comment": comment,
                        "rating": rating,
                        "id_order" : id_order
                    },
                    beforeSend: function () {
                        $("#loading_event").fadeIn(400);
                    },
                    success: function (resultdata) {
                        $("#loading_event").fadeOut(400);

                        resultauthdata = jQuery.parseJSON(resultdata);

                        if (resultauthdata.status == 'ok') {
                            $('#result_add_event').fadeIn(400);

                            //Для начала отчистим поля
                            switch (event_type)
                            {
                                case 'callback-communication':
                                    $('textarea[name="comment_for_callback_communication"]').val('');
                                    break;
                                case 'callback-org_info':
                                    $('textarea[name="comment_for_callback_org_info"]').val('');
                                    break;
                                case 'company-long_time_pay':
                                    $('input[name="id_order_for_company_long_time_pay"]').val('');
                                    $('textarea[name="comment_for_company_long_time_pay"]').val('');
                                    break;
                                case 'company-no_pay':
                                    $('input[name="id_order_for_company_no_pay"]').val('');
                                    $('textarea[name="comment_for_company_no_pay"]').val('');
                                    break;
                                case 'comment':
                                    $('#dop_info_for_event div').hide(0);
                                    $('#dop_info_for_event #comment').fadeIn(400);

                                    ok_submit = true;
                                    break;
                            }

                            setTimeout(function () {
                                $('#add_event').modal('hide');

                                $('#add_event').on('hidden.bs.modal', function () {

                                    $('#result_add_event').hide(0);

                                    showProfile(id);
                                });
                            }, 1000);
                        }
                        else if(resultauthdata.status == 'error_check')
                        {
                            swal("Ошибка", resultauthdata.text, "error");
                        }
                        else {
                            swal("Ошибка", "Не удалось добавить событие - ошибка на сервере. Обратитесь к админу", "error");
                        }
                    },
                    error: function () {
                        $("#loading_update").fadeOut(400);
                        swal("Ошибка", "Не удалось добавить событие. Обратитесь к админу", "error");
                    }
                });
            }
        }

        function showUpdateStatus(id, name_lastname)
        {
            $('#update_status #us_spec_name').text(name_lastname);

            $('#btn_up_status').attr('onclick', 'updateStatus('+id+')');

            $('#mini_profile').modal('hide');

            $('#update_status').modal('show');
        }

        function showEvent(id, name_lastname)
        {
            $('#add_event #aec_spec_name').text(name_lastname);

            $('#btn_add_event').attr('onclick', 'addEventForSpecialist('+id+')');

            $('#mini_profile').modal('hide');

            $('#add_event').modal('show');
        }

        function showDopInfoForEvent()
        {
            switch ($('#id_event_type').val())
            {
                case 'callback-communication':
                    $('#dop_info_for_event div').hide(0);
                    $('#dop_info_for_event #callback_communication').fadeIn(400);
                    break;
                case 'callback-org_info':
                    $('#dop_info_for_event div').hide(0);
                    $('#dop_info_for_event #callback_org_info').fadeIn(400);
                    break;
                case 'company-long_time_pay':
                    $('#dop_info_for_event div').hide(0);
                    $('#dop_info_for_event #company_long_time_pay').fadeIn(400);
                    break;
                case 'company-no_pay':
                    $('#dop_info_for_event div').hide(0);
                    $('#dop_info_for_event #company_no_pay').fadeIn(400);
                    break;
                case 'comment':
                    $('#dop_info_for_event div').hide(0);
                    $('#dop_info_for_event #comment').fadeIn(400);
                    break;
            }
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

