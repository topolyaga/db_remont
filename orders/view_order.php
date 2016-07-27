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

$id_order = $_GET['id'];

$stmt=$db->prepare("SELECT `id`, `id_num_day`, `id_call`, `id_manager`, `id_specialist`, DATE_FORMAT( add_date,  '%d.%m.%Y в %H:%i' ) as add_date, `about_object`, `have_materials`, `preparatory`, `price_start`, `price_finish`, DATE_FORMAT( date_view,  '%d.%m.%Y' ) as date_view, DATE_FORMAT( date_work_start,  '%d.%m.%Y' ) as date_work_start, DATE_FORMAT( date_work_finish,  '%d.%m.%Y' ) as date_work_finish, `address`, `client_name`, `client_phone`, `cpec_categorys`, `prioritet`, `percent`, `estimated_price`, `calculated_price`, `finish_price`, `status` FROM `orders` WHERE id=:id_order");
$stmt->execute(array("id_order"=>$id_order));
$order_info=$stmt->fetch(PDO::FETCH_ASSOC);

//Узнаем события по заказу

$status_events = array();

//События статуса. Принятие в обработку - самое первое событие обработки
$stmt=$db->prepare("SELECT DATE_FORMAT(add_date,  '%d.%m.%Y в %H:%i' ) as date_time FROM `events` WHERE type='order' && id_ansubject=:id_order && now_status='Принят в обработку' ORDER BY `events`.`id` ASC LIMIT 0,1");
$stmt->execute(array("id_order"=>$order_info['id']));
$tmp=$stmt->fetch(PDO::FETCH_ASSOC);

$status_events['start'] = $tmp['date_time'];

//События статуса. Назначение исполнителя - последние событие назвачения
$stmt=$db->prepare("SELECT DATE_FORMAT(add_date,  '%d.%m.%Y в %H:%i' ) as date_time FROM `events` WHERE type='order' && id_ansubject=:id_order && now_status='Назначен исполнитель' && sub_type='spec_add' ORDER BY `events`.`id` DESC LIMIT 0,1");
$stmt->execute(array("id_order"=>$order_info['id']));
$tmp=$stmt->fetch(PDO::FETCH_ASSOC);

$status_events['selected_specialis'] = $tmp['date_time'];

//События статуса. Осмотр объекта - последние событие назвачения
$stmt=$db->prepare("SELECT DATE_FORMAT(selected_date_time,  '%d.%m.%Y в %H:%i' ) as date_time FROM `events` WHERE type='order' && id_ansubject=:id_order && now_status='Осмотр объекта' ORDER BY `events`.`id` DESC LIMIT 0,1");
$stmt->execute(array("id_order"=>$order_info['id']));
$tmp=$stmt->fetch(PDO::FETCH_ASSOC);

$status_events['view_object'] = $tmp['date_time'];

//События статуса. Исполнение - последние событие назвачения
$stmt=$db->prepare("SELECT DATE_FORMAT(selected_date_time,  '%d.%m.%Y в %H:%i' ) as date_time FROM `events` WHERE type='order' && id_ansubject=:id_order && now_status='Исполняется' ORDER BY `events`.`id` DESC LIMIT 0,1");
$stmt->execute(array("id_order"=>$order_info['id']));
$tmp=$stmt->fetch(PDO::FETCH_ASSOC);

$status_events['start_work'] = $tmp['date_time'];

//События статуса. Завершение работы - последние событие назвачения
$stmt=$db->prepare("SELECT DATE_FORMAT(selected_date_time,  '%d.%m.%Y в %H:%i' ) as date_time FROM `events` WHERE type='order' && id_ansubject=:id_order && now_status='Исполнен' ORDER BY `events`.`id` DESC LIMIT 0,1");
$stmt->execute(array("id_order"=>$order_info['id']));
$tmp=$stmt->fetch(PDO::FETCH_ASSOC);

$status_events['finish_work'] = $tmp['date_time'];

//События статуса. Закрытие - последние событие назвачения
$stmt=$db->prepare("SELECT DATE_FORMAT(add_date,  '%d.%m.%Y в %H:%i' ) as date_time FROM `events` WHERE type='order' && id_ansubject=:id_order && (now_status='Закрыт' || now_status='Отменен') ORDER BY `events`.`id` DESC LIMIT 0,1");
$stmt->execute(array("id_order"=>$order_info['id']));
$tmp=$stmt->fetch(PDO::FETCH_ASSOC);

$status_events['close'] = $tmp['date_time'];


//Выделим в отдельный массив информацию о необходимых спецах - категории

$categorys = array();

//Узнаем выбранные категории
$ids_categorys = explode(",", $order_info['cpec_categorys']);

//Достаем все категории
$stmt=$db->prepare("SELECT * FROM `specialist_categorys` WHERE 1");
$stmt->execute();

$i=0;

while($category=$stmt->fetch(PDO::FETCH_ASSOC))
{
    foreach($ids_categorys as $val)
    {
        if($val == $category['id'])
        {
            $categorys[$i]=array("id"=>$category['id'], "title"=>$category['title']);
        }
    }

    $i++;
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
                <h2 class="h-str">Заказ #<?=$order_info['id']?></h2>
                <input type="hidden" name="id_order" value="<?=$order_info['id']?>">
            </div>

            <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
                <div class="content-box order-info">
                    <div class="row">
                        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
                            <p class="client-name-phone"><?=$order_info['client_name']?> <?=$order_info['client_phone']?></p>
                            <p class="client-address"><?=$order_info['address']?></p>
                        </div>

                        <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                            <table class="tb-pri-per" cellpadding="0" cellspacing="0" border="0">
                                <thead>
                                    <tr>
                                        <?
                                        switch($order_info['prioritet'])
                                        {
                                            case 1: $background_th = 'rgb(217, 83, 79)'; $background_td = 'rgba(217, 83, 79, 0.8)';  break;
                                            case 2: $background_th = 'rgb(240, 173, 78)'; $background_td = 'rgba(240, 173, 78, 0.8)';  break;
                                            case 3: $background_th = 'rgb(102, 102, 102)'; $background_td = 'rgba(102, 102, 102, 0.8)';  break;
                                        }
                                        ?>
                                        <th class="prioritet" style="background: <?=$background_th?>;">Приоритет</th>
                                        <th class="percent">Процент</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="prioritet" style="background: <?=$background_td?>;">
                                            <?
                                                switch($order_info['prioritet'])
                                                {
                                                    case 1: echo 'Высокий'; break;
                                                    case 2: echo 'Обычный'; break;
                                                    case 3: echo 'Низкий'; break;
                                                }
                                            ?>
                                        </td>
                                        <td class="percent"><?=$order_info['percent']?>%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="about-object col-xs-12 col-sm-12 col-md-12 col-lg-12"><?=nl2br($order_info['about_object'])?></div>
                    </div>

                    <div class="row">
                        <div class="order-dop-info col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <p class="text-label" style="font-size: 20px; margin: 0px 0 20px 0;">Дополнительная информация</p>

                            <table class="table table-hover table-bordered more-info">
                                <tbody>
                                <tr>
                                    <td class="h" width="50%">Наличие материала</td>
                                    <td width="50%"><strong><? if($order_info['have_materials']=='1'){echo 'Да';}else{echo 'Нет';}?></strong></td>
                                </tr>
                                <tr>
                                    <td class="h">Подготовительные работы</td>
                                    <td><strong><? if($order_info['preparatory']=='1'){echo 'Нужны';}else{echo 'Не нужны';}?></strong></td>
                                </tr>
                                <tr>
                                    <td class="h">Диапозон цены</td>
                                    <td><strong><?='от '.$order_info['price_start'].' до '.$order_info['price_finish'].' руб'?></strong></td>
                                </tr>
                                <tr>
                                    <td class="h">Желаемая дата осмотра</td>
                                    <td><strong><?=$order_info['date_view']?></strong></td>
                                </tr>
                                <tr>
                                    <td class="h">Сроки</td>
                                    <td><strong><?='c '.$order_info['date_work_start'].' по '.$order_info['date_work_finish']?></strong></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="order-spec-cat col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <p class="text-label" style="font-size: 20px; margin: 0px 0 20px 0;">Категории специалистов</p>

                            <?
                                foreach($categorys as $val)
                                {
                                    ?>
                                    <a href="http://<?=$_SERVER['HTTP_HOST']?>/specialisti/list.php?category=<?=$val['id']?>&date_view=<?=$order_info['date_view']?>" class="btn btn-default" style="font-size: 14px;"><?=$val['title']?></a>
                                    <?
                                }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <h3 class="h-str">Исполнение заказа</h3>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="content-box" style="padding: 0 0 0 0;">
                            <?
                            switch($order_info['status'])
                            {
                                case 'Новый': $progress_order = '0%'; break;
                                case 'Приценочный': $progress_order = '0%'; break;

                                case 'Принят в обработку': $progress_order = '20%'; break;
                                case 'Назначен исполнитель': $progress_order = '40%'; break;
                                case 'Осмотр объекта': $progress_order = '50%'; break;

                                case 'Исполняется': $progress_order = '60%'; break;
                                case 'Исполнен': $progress_order = '80%'; break;

                                case 'Закрыт': $progress_order = '100%'; break;

                                case 'Отменен': $progress_order = '100%'; break;
                            }
                            ?>
                            <div class="progress" style="margin: 0 0 0 0; height: 40px; border-radius: 4px 4px 0 0; box-shadow: none; background: #fff;border-bottom: 1px solid #f6f6f6;">
                                <div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="box-shadow: none; width: <?=$progress_order?>;">
                                    <span class="sr-only" style="position: relative; clip: auto; top: 10px; font-size: 14px;">Выполнение заказа - <?=$progress_order?></span>
                                </div>
                            </div>

                            <div class="order-task">
                                <p class="h">Текущая задача</p>
                                <?
                                    //Задаем задания
                                    $task = '';

                                    switch($order_info['status'])
                                    {
                                        case 'Новый':
                                            ?>
                                            <p class="title">Принять заказ в обработку</p>

                                            <form action="work/go_status_process.php" name="go_status_process" method="post">
                                                <input type="hidden" name="id_order_for_status_process" value="<?=$order_info['id']?>">
                                                <button type="submit" class="btn btn-warning" style="font-size: 16px; width: 100%;">Принять в обработку</button>
                                            </form>

                                            <?
                                            break;

                                        case 'Приценочный':
                                            ?>
                                            <p class="title">Убедить клиента сделать заказ</p>


                                            <?
                                            break;

                                        case 'Принят в обработку':
                                            ?>
                                            <p class="title">Указать ориентировочную сумму заказа,<br>найти и назначить исполнителя</p>

                                            <div class="btn-group btn-group-justified" style="text-align: center" role="group" aria-label="...">

                                                <?
                                                    //Сформируем доп к ссылке на спецов
                                                    $dop_parametrs = '?onsearch=1';

                                                    foreach($ids_categorys as $val)
                                                    {
                                                        $dop_parametrs.='&categorys%5B%5D='.$val;
                                                    }

                                                    $dop_parametrs.='&type_category=ili&status=Любойкчс&add_spec_for_order=1&id_order='.$id_order;

                                                ?>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-default" onclick="showUpdateEstimatedPrice();">Указать сумму заказа</button>
                                                </div>

                                                <div class="btn-group" role="group">
                                                    <a href="http://<?=$_SERVER['HTTP_HOST']?>/specialisti/list.php<?=$dop_parametrs?>" target="_blank" class="btn btn-default">Поиск специалиста</a>
                                                </div>

                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-default" onclick="showAddSpecialist();">Назначить исполнителя</button>

                                                </div>
                                            </div>

                                            <?
                                            break;

                                        case 'Назначен исполнитель':
                                            ?>
                                                <p class="title">Передать контактные данные заказчика,<br>получить и зафиксировать обратную связь</p>

                                                <div class="btn-group btn-group-justified" style="text-align: center" role="group" aria-label="...">
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-default" onclick="showSendContacts();">Передача контактных данных</button>
                                                    </div>

                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-default" onclick="showAddEventCallbackASC();">Добавить событие для исполнителя</button>
                                                    </div>
                                                </div>
                                            <?
                                            break;
                                        case 'Осмотр объекта': $bg_color_class = 'os-process'; $progress_order = '50%'; break;

                                        case 'Исполняется': $bg_color_class = 'os-work-process'; $progress_order = '60%'; break;
                                        case 'Исполнен': $bg_color_class = 'os-work-process'; $progress_order = '80%'; break;

                                        case 'Закрыт': $bg_color_class = 'os-success'; $progress_order = '100%'; break;

                                        case 'Отменен': $bg_color_class = 'os-canceled'; $progress_order = '100%'; break;
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <h3 class="h-str">Лента действий с заказом</h3>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="content-box events-log" style="padding: 0 0 0 0; font-size: 16px;">
                            <?
                            $stmt=$db->prepare("SELECT `id`, DATE_FORMAT( add_date,  '%d.%m.%Y в %H:%i' ) as add_date, `sub_type`, `id_sender`, `dop_id_subject_1`, `dop_id_subject_2`, `selected_date_time`, `now_status`, `comment` FROM `events` WHERE type='order' && id_ansubject=:id_order ORDER BY `events`.`id` DESC");
                            $stmt->execute(array("id_order"=>$order_info['id']));

                            $i=0;

                            while($events=$stmt->fetch(PDO::FETCH_ASSOC))
                            {
                                //Подготовим к выводу

                                //Значек события
                                switch($events['sub_type'])
                                {
                                    case 'sys_info': $icon = 'icon-info-sign'; break;
                                    case 'working': $icon = 'icon-refresh'; break;

                                    case 'spec_add': $icon = 'icon-addfriend'; break;
                                    case 'spec_del': $icon = 'icon-removefriend'; break;

                                    case 'send_contacts': $icon = 'icon-phone-call'; break;
                                    case 'view_object': $icon = 'icon-eye-view'; break;
                                    case 'spec_work': $icon = 'icon-websitebuilder'; break;
                                    case 'spec_end_work': $icon = 'icon-websitebuilder'; break;
                                    case 'callback_spec': $icon = 'icon-incomingcall'; break;

                                    case 'close': $icon = 'icon-ok'; break;
                                    case 'canceled': $icon = 'icon-remove'; break;

                                    case 'comment': $icon = 'icon-commenttyping'; break;

                                    case 'update_info': $icon = 'icon-rawaccesslogs'; break;
                                }

                                switch($events['now_status'])
                                {
                                    case 'Новый': $bg_color_class = 'ei-new'; break;
                                    case 'Приценочный': $bg_color_class = 'ei-preprice'; break;

                                    case 'Принят в обработку': $bg_color_class = 'ei-process'; break;
                                    case 'Назначен исполнитель': $bg_color_class = 'ei-process'; break;
                                    case 'Осмотр объекта': $bg_color_class = 'ei-process'; break;

                                    case 'Исполняется': $bg_color_class = 'ei-work-process'; break;
                                    case 'Исполнен': $bg_color_class = 'ei-work-process'; break;

                                    case 'Закрыт': $bg_color_class = 'ei-success'; break;

                                    case 'Отменен': $bg_color_class = 'ei-canceled'; break;
                                }

                                ?>
                                    <div class="event">
                                        <div class="event-info <?=$bg_color_class?>">
                                            <i class="<?=$icon?>"></i>
                                            <p class="add-date"><?=$events['add_date']?></p>
                                            <p class="status"><?=$events['now_status']?></p>
                                        </div>
                                        <div class="comment"><?=nl2br($events['comment'])?></div>
                                    </div>
                                <?
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                <?
                    switch($order_info['status'])
                    {
                        case 'Новый': $bg_color_class = 'os-new'; $progress_order = '0%'; break;
                        case 'Приценочный': $bg_color_class = 'os-preprice'; $progress_order = '0%'; break;

                        case 'Принят в обработку': $bg_color_class = 'os-process'; $progress_order = '20%'; break;
                        case 'Назначен исполнитель': $bg_color_class = 'os-process'; $progress_order = '40%'; break;
                        case 'Осмотр объекта': $bg_color_class = 'os-process'; $progress_order = '50%'; break;

                        case 'Исполняется': $bg_color_class = 'os-work-process'; $progress_order = '60%'; break;
                        case 'Исполнен': $bg_color_class = 'os-work-process'; $progress_order = '80%'; break;

                        case 'Закрыт': $bg_color_class = 'os-success'; $progress_order = '100%'; break;

                        case 'Отменен': $bg_color_class = 'os-canceled'; $progress_order = '100%'; break;
                    }
                ?>
                <div class="order-status <?=$bg_color_class?>"><?=$order_info['status']?></div>

<!--                <div class="progress" style="margin: 0 0 0 0; border-radius: 0; box-shadow: none; background: #fff;border-bottom: 1px solid #f6f6f6;">-->
<!--                    <div class="progress-bar progress-bar-danger progress-bar-striped active" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="box-shadow: none; width: -->
<!--                        <span class="sr-only" style="position: relative; clip: auto;">Выполнение заказа - <!--</span>-->
<!--                    </div>-->
<!--                </div>-->

                <div class="content-box order-sys-info" style="padding: 5px 0;">
                    <ul>
                        <li>Поступил<span><?=$order_info['add_date']?></span></li>
                        <li>Принят в обработку<span><?=$status_events['start']?></span></li>
                        <li>Назначен исполнитель<span><?=$status_events['selected_specialis']?></span></li>
                        <li>Осмотр объекта<span><?=$status_events['view_object']?></span></li>
                        <li>Начало работ<span><?=$status_events['start_work']?></span></li>
                        <li>Окончание работ<span><?=$status_events['finish_work']?></span></li>
                        <li>Закрытие<span><?=$status_events['close']?></span></li>
                    </ul>
                </div>

                <div class="ispolnitel">
                    <h3 class="h-str" style="font-size: 20px;">Исполнитель</h3>

                    <?
                        if($order_info['id_specialist']==0)
                        {
                            ?><p style="font-size: 16px;font-weight: lighter;">Исполнитель не назначен</p><?
                        }
                        else
                        {
                            $stmt=$db->prepare("SELECT `id`, `name`, `lastname`, `avatar_link`, `phone`, `phone2` FROM `specialist_db` WHERE id=:id_specilaist");
                            $stmt->execute(array("id_specilaist"=>$order_info['id_specialist']));
                            $specialist_info=$stmt->fetch(PDO::FETCH_ASSOC);

                            ?>
                            <div class="row">
                                <a href="http://<?=$_SERVER['HTTP_HOST']?>/specialisti/view_profile.php?id=<?=$specialist_info['id']?>" style="color: #333;" target="_blank">
                                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                        <div class="content-box ispol-info" style="padding: 0 0 0 0;">
                                            <div class="avatar" style="background: url(<?=$specialist_info['avatar_link']?>) no-repeat;"></div>

                                            <div class="info">
                                                <p class="name"><?=$specialist_info['name'].' '.$specialist_info['lastname']?></p>
                                                <p class="phone"><?=$specialist_info['phone']?><br><?=$specialist_info['phone2']?></p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?
                        }
                    ?>
                </div>

                <div class="finish_price">
                    <h3 class="h-str" style="font-size: 20px; margin-bottom: 10px;">Итоговая сумма заказа</h3>

                    <?
                    if($order_info['finish_price']==0)
                    {
                        ?><p style="font-size: 16px;font-weight: lighter;">Сумма еще не указана</p><?
                    }
                    else
                    {
                        ?>
                            <p style="font-size: 22px;font-weight: bold;">
                                <?=number_format($order_info['finish_price'], 0, '.', ' ').' руб'?>
                                <span style="font-size: 16px; font-weight: lighter;"> x 0.<?=$order_info['percent']?> = </span>
                                <span style="font-style: italic; color: #f00;"><?=number_format($order_info['finish_price']*($order_info['percent']/100), 0, '.', ' ').' руб'?></span>
                            </p>
                        <?
                    }
                    ?>
                </div>

                <div class="calculated_price">
                    <h3 class="h-str" style="font-size: 20px; margin-bottom: 10px;">Рассчитаная сумма заказа</h3>

                    <?
                    if($order_info['calculated_price']==0)
                    {
                        ?><p style="font-size: 16px;font-weight: lighter;">Сумма еще не указана</p><?
                    }
                    else
                    {
                        ?>
                        <p style="font-size: 22px;font-weight: bold;">
                            <?=number_format($order_info['calculated_price'], 0, '.', ' ').' руб'?>
                            <span style="font-size: 16px; font-weight: lighter;"> x 0.<?=$order_info['percent']?> = </span>
                            <span style="font-style: italic; color: #f00;"><?=number_format($order_info['calculated_price']*($order_info['percent']/100), 0, '.', ' ').' руб'?></span>
                        </p>
                        <?
                    }
                    ?>
                </div>

                <div class="estimated_price">
                    <h3 class="h-str" style="font-size: 20px; margin-bottom: 10px;">Ориентировочная сумма заказа</h3>

                    <?
                    if($order_info['estimated_price']==0)
                    {
                        ?><p style="font-size: 16px;font-weight: lighter;">Сумма еще не указана</p><?
                    }
                    else
                    {
                        ?>
                        <p style="font-size: 22px;font-weight: bold;">
                            <?=number_format($order_info['estimated_price'], 0, '.', ' ').' руб'?>
                            <span style="font-size: 16px; font-weight: lighter;"> x 0.<?=$order_info['percent']?> = </span>
                            <span style="font-style: italic; color: #f00;"><?=number_format($order_info['estimated_price']*($order_info['percent']/100), 0, '.', ' ').' руб'?></span>
                        </p>
                        <?
                    }
                    ?>
                </div>

                <div class="order-actions">
                    <?
                        if($order_info['status']=='Новый')
                        {
                            ?>
<!--                            <form action="work/go_status_process.php" name="go_status_process" method="post">-->
<!--                                <input type="hidden" name="id_order_for_status_process" value="--><?//=$order_info['id']?><!--">-->
<!--                                <button type="submit" class="btn btn-warning" style="font-size: 16px; width: 100%;">Принять в обработку</button>-->
<!--                            </form>-->
                            <?
                        }
                        else
                        {
                            ?>
                            <div class="btn-group" style="width: 100%;">
                                <button type="button" class="btn btn-warning dropdown-toggle" style="font-size: 16px; width: 100%;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Действия с заказом <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" style="width: 100%;">
                                    <li class="dropdown-header">Заказ</li>

                                    <li><a onclick="showAddComment();">Добавить комментарий</a></li>

                                    <li><a onclick="showUpdateFinishPrice();">Указать итоговую сумму заказа</a></li>
                                    <li><a onclick="showUpdateCalculatedPrice();">Указать рассчитаную сумму заказу</a></li>
                                    <li><a onclick="showUpdateEstimatedPrice();">Указать ориентировочную сумму заказа</a></li>

                                    <li><a>Редактировать информацию</a></li>

                                    <li role="separator" class="divider"></li>

                                    <li class="dropdown-header">Исполнитель</li>

                                    <?
                                    if($order_info['id_specialist']==0)
                                    {
                                        ?>
                                            <li><a onclick="showAddSpecialist();">Назначить исполнителя</a></li>
                                        <?
                                    }
                                    else
                                    {
                                        ?>
                                            <li><a onclick="showDelSpecialist();">Снять исполнителя</a></li>
                                            <li><a href="#">Отправить контакты заказчика</a></li>
                                            <li><a href="#">Добавить событие</a></li>
                                        <?
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?
                        }
                    ?>
                </div>



            </div>
        </div>
    </div>

    <? require('../inc/sys/footer.php'); ?>

    <div class="modal fade" id="add_specialist" tabindex="-1" role="dialog" aria-labelledby="order_add_specialist">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Назначение исполнителя</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <p style="text-align: center; font-size: 18px; font-weight: lighter; margin: 0 0 20px 0;">Выберите исполнителя</p>
                        </div>

                        <div id="par_spec_space" class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <table class="table table-hover table-bordered table-spec" id="spec_space">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th colspan="2">Специалист</th>
                                        <th>Категория</th>
                                        <th>Статус</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?
                                $query = 'SELECT `id`, `name`, `lastname`, `avatar_link`, `id_categorys`, `phone`, `min_price`, `min_price_opt`, `min_price_for_order`, `areas`, `status` FROM `specialist_db` WHERE 1';
                                $stmt = $db->prepare($query);
                                $stmt->execute();

                                while($specialisti = $stmt->fetch(PDO::FETCH_ASSOC))
                                {
                                ?>
                                <tr id="spec_<?=$specialisti['id']?>">
                                    <td valign="middle" align="center" width="50" style="font-weight: bold; "><?=$specialisti['id']?></td>

                                    <td class="ava-block" width="66" style="border: none; border-bottom: 1px solid #ddd;">
                                        <div class="avatar" style="background: url(<? if($specialisti['avatar_link']!=''){echo $specialisti['avatar_link'];}else{echo 'http://'.$_SERVER['HTTP_HOST'].'/img/no_photo.jpg';}?>) no-repeat center;"></div>
                                    </td>

                                    <td class="spec-info" width="200" style="border: none; border-bottom: 1px solid #ddd;">
                                        <p class="name"><?=$specialisti['name'].' '.$specialisti['lastname']?></p>
                                        <p class="phone"><?=$specialisti['phone']?></p>
                                    </td>

                                    <td style="vertical-align: top !important; max-width: 180px;">
                                        <?
                                        $ids_categorys = explode(",", $specialisti['id_categorys']);

                                        $str_categorys = '';

                                        //Достаем все категории
                                        $stmt1=$db->prepare("SELECT * FROM `specialist_categorys` WHERE 1");
                                        $stmt1->execute();

                                        $category_titles = array();

                                        while($tmp_category=$stmt1->fetch(PDO::FETCH_ASSOC))
                                        {

                                            $category_titles[$tmp_category['id']] = $tmp_category['title'];
                                        }

                                        foreach($ids_categorys as $val)
                                        {
                                            $str_categorys.=$category_titles[$val].', ';
                                        }

                                        $str_categorys = substr($str_categorys, 0, -2);

                                        echo $str_categorys;
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
                                    <td style="text-align: center;"><button class="btn btn-warning" style="font-size: 14px;" onclick="addSpecialistForOrder(<?=$specialisti['id']?>);">Назначить</button></td>
                                </tr>
                                <?
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 20px;">

                            <div id="loading_add_specialist" class="loading"></div>

                            <p id="result_add_specialist" style="font-size: 16px; color: #f00; font-weight: bold; display: none;">Специалист успешно назначен исполнителем</p>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" style="font-size: 14px;" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="del_specialist" tabindex="-1" role="dialog" aria-labelledby="order_del_specialist">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Снятие исполнителя с заказа</h4>
                </div>
                <div class="modal-body">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <p style="text-align: center; font-size: 18px; font-weight: lighter; margin: 0 0 20px 0;">Снятие исполнителя<br><?=$specialist_info['name'].' '.$specialist_info['lastname']?> с заказа #<?=$id_order?></p>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <p class="text-label">Укажите степень доставленных неудобств</p>
                            <select id="cause_cut">
                                <option value="Нейтральная">Нейтральная</option>
                                <option value="Негативная (низкий)">Негативная (низкий)</option>
                                <option value="Негативная (средний)">Негативная (средний)</option>
                                <option value="Негативная (высокий)">Негативная (высокий)</option>
                            </select>

                            <p class="text-label">Опишите причину (обязательно)</p>
                            <textarea name="comment_cut" style="width: 100%; height: 120px; font-size: 16px;" placeholder="Введите комментарий..."></textarea>

                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 20px;">

                                    <div id="loading_del_specialist" class="loading"></div>

                                    <p id="result_del_specialist" style="font-size: 16px; color: #f00; font-weight: bold; display: none;">Исполнитель успешно снят с заказа</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                                    <button type="button" class="btn btn-success" style="font-size: 14px;" onclick="delSpecialistForOrder(<?=$specialist_info['id']?>);">Снять исполнителя</button>
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


    <div class="modal fade" id="add_comment" tabindex="-1" role="dialog" aria-labelledby="order_add_comment">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Добавление комментария к заказу</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <textarea name="comment" style="width: 100%; height: 70px; font-size: 16px;" placeholder="Введите комментарий..."></textarea>

                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 20px;">

                                    <div id="loading_add_comment" class="loading"></div>

                                    <p id="result_add_comment" style="font-size: 16px; color: #f00; font-weight: bold; display: none;">Комментарий успешно добавлен</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                                    <button type="button" class="btn btn-success" style="font-size: 14px;" onclick="addComment();">Добавить комментарий</button>
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

    <div class="modal fade" id="update_finish_price" tabindex="-1" role="dialog" aria-labelledby="order_update_finish_price">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Указание итоговой суммы заказа</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
                            <input type="number" name="finish_price" value="<?=$order_info['finish_price']?>">
                        </div>

                        <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                            <button type="button" class="btn btn-success" style="font-size: 14px; width: 100%; padding: 12px 20px;" onclick="UpdateFinishPrice();">Обновить</button>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 20px;">

                            <div id="loading_update_finish_price" class="loading"></div>

                            <p id="result_update_finish_price" style="font-size: 16px; color: #f00; font-weight: bold; display: none;">Итоговая сумма изменена</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" style="font-size: 14px;" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="update_calculated_price" tabindex="-1" role="dialog" aria-labelledby="order_update_calculated_price">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Указание рассчитаной суммы заказа</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
                            <input type="number" name="calculated_price" value="<?=$order_info['calculated_price']?>">
                        </div>

                        <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                            <button type="button" class="btn btn-success" style="font-size: 14px; width: 100%; padding: 12px 20px;" onclick="UpdateCalculatedPrice();">Обновить</button>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 20px;">

                            <div id="loading_update_calculated_price" class="loading"></div>

                            <p id="result_update_calculated_price" style="font-size: 16px; color: #f00; font-weight: bold; display: none;">Рассчитаная сумма изменена</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" style="font-size: 14px;" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="update_estimated_price" tabindex="-1" role="dialog" aria-labelledby="order_update_estimated_price">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Указание ориентировочной суммы заказа</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
                            <input type="number" name="estimated_price" value="<?=$order_info['estimated_price']?>">
                        </div>

                        <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4">
                            <button type="button" class="btn btn-success" style="font-size: 14px; width: 100%; padding: 12px 20px;" onclick="updateEstimatedPrice();">Обновить</button>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 20px;">

                            <div id="loading_update_estimated_price" class="loading"></div>

                            <p id="result_update_estimated_price" style="font-size: 16px; color: #f00; font-weight: bold; display: none;">Ориентировачная сумма изменена</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" style="font-size: 14px;" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="send_contacts" tabindex="-1" role="dialog" aria-labelledby="order_send_contacts">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Передача контактных данных исполнителю</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="btn-group btn-group-justified" role="group" aria-label="...">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-default" style="font-size: 14px;" onclick="sendSmsWithContacts();">Отправить смс</button>
                                </div>

                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-default" style="font-size: 14px;" onclick="sendSoloContacts();">Сам передал контакты</button>
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 20px;">

                            <div id="loading_send_contacts" class="loading"></div>

                            <p id="pending_send_contacts" style="font-size: 16px; color: #f00; font-weight: bold; display: none;">Смс отправляется</p>
                            <p id="result_send_contacts" style="font-size: 16px; color: #f00; font-weight: bold; display: none;"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" style="font-size: 14px;" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_event_callback_asc" tabindex="-1" role="dialog" aria-labelledby="order_add_event_callback_asc">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Обратная связь от исполнителя после связи с заказчиком</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

                            <p class="text-label">Результат</p>

                            <select id="result_callback_after_says" onchange="toggleDopInputsForCASC();">
                                <option>Выбрать</option>

                                <option value="view">Договорились об осмотре объекта</option>
                                <option value="renouncement">Отказ от заказа</option>
                                <option value="cut">Не связался с заказчиком</option>
                            </select>

                            <div class="row">
                                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                                    <p class="text-label">Дата осмотра</p>
                                    <input name="date_object_view" type="date">
                                </div>
                                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                                    <p class="text-label">Время осмотра</p>
                                    <input name="time_object_view" type="time">
                                </div>
                            </div>



                            <p class="text-label">Ответ специалиста</p>

                            <textarea name="answer_spec" style="width: 100%; height: 70px; font-size: 16px;" placeholder="Введите комментарий..."></textarea>
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center" style="margin-top: 20px;">

                            <div id="loading_send_contacts" class="loading"></div>

                            <p id="pending_send_contacts" style="font-size: 16px; color: #f00; font-weight: bold; display: none;">Смс отправляется</p>
                            <p id="result_send_contacts" style="font-size: 16px; color: #f00; font-weight: bold; display: none;"></p>
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
    <script src="http://<?=$_SERVER['HTTP_HOST']?>/js/sweet-alert.js"></script>

<script>
    function updateStatus()
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

    function updateEstimatedPrice()
    {
        var id_order = $('input[name="id_order"]').val();
        var estimated_price = $('input[name="estimated_price"]').val();

        $.ajax({
            type: "POST",
            url: 'work/update_order_info.php',
            data: {"id_order" : id_order, "type" : "estimated_price", "estimated_price" : estimated_price},
            beforeSend: function()
            {
                $("#loading_update_estimated_price").fadeIn(400);
            },
            success: function(resultdata)
            {
                $("#loading_update_estimated_price").fadeOut(400);

                resultauthdata=jQuery.parseJSON(resultdata);

                if(resultauthdata.status=='ok')
                {
                    $('#result_update_estimated_price').fadeIn(400);

                    setTimeout(function(){
                        window.location.reload();
                    }, 1000);
                }
                else
                {
                    swal("Ошибка", "Не удалось обновить ориентировочную сумму заказа - ошибка на сервере. Обратитесь к админу", "error");
                }
            },
            error: function()
            {
                $("#loading_update_estimated_price").fadeOut(400);
                swal("Ошибка", "Не удалось обновить ориентировочную сумму заказа. Обратитесь к админу", "error");
            }
        });
    }

    function UpdateCalculatedPrice()
    {
        var id_order = $('input[name="id_order"]').val();
        var calculated_price = $('input[name="calculated_price"]').val();

        $.ajax({
            type: "POST",
            url: 'work/update_order_info.php',
            data: {"id_order" : id_order, "type" : "calculated_price", "calculated_price" : calculated_price},
            beforeSend: function()
            {
                $("#loading_update_calculated_price").fadeIn(400);
            },
            success: function(resultdata)
            {
                $("#loading_update_calculated_price").fadeOut(400);

                resultauthdata=jQuery.parseJSON(resultdata);

                if(resultauthdata.status=='ok')
                {
                    $('#result_update_calculated_price').fadeIn(400);

                    setTimeout(function(){
                        window.location.reload();
                    }, 1000);
                }
                else if(resultauthdata.status=='error_no_spec')
                {
                    swal("Ошибка", resultauthdata.text, "error");
                }
                else if(resultauthdata.status=='error_no_callback_spec')
                {
                    swal("Ошибка", resultauthdata.text, "error");
                }
                else
                {
                    swal("Ошибка", "Не удалось обновить рассчитаную сумму заказа - ошибка на сервере. Обратитесь к админу", "error");
                }
            },
            error: function()
            {
                $("#loading_update_calculated_price").fadeOut(400);
                swal("Ошибка", "Не удалось обновить рассчитаную сумму заказа. Обратитесь к админу", "error");
            }
        });
    }

    function UpdateFinishPrice()
    {
        var id_order = $('input[name="id_order"]').val();
        var finish_price = $('input[name="finish_price"]').val();

        $.ajax({
            type: "POST",
            url: 'work/update_order_info.php',
            data: {"id_order" : id_order, "type" : "finish_price", "finish_price" : finish_price},
            beforeSend: function()
            {
                $("#loading_update_finish_price").fadeIn(400);
            },
            success: function(resultdata)
            {
                $("#loading_update_finish_price").fadeOut(400);

                resultauthdata=jQuery.parseJSON(resultdata);

                if(resultauthdata.status=='ok')
                {
                    $('#result_update_finish_price').fadeIn(400);

                    setTimeout(function(){
                        window.location.reload();
                    }, 1000);
                }
                else if(resultauthdata.status=='error_no_spec')
                {
                    swal("Ошибка", resultauthdata.text, "error");
                }
                else if(resultauthdata.status=='error_no_spec_end_work')
                {
                    swal("Ошибка", resultauthdata.text, "error");
                }
                else
                {
                    swal("Ошибка", "Не удалось обновить итоговую сумму заказа - ошибка на сервере. Обратитесь к админу", "error");
                }
            },
            error: function()
            {
                $("#loading_update_finish_price").fadeOut(400);
                swal("Ошибка", "Не удалось обновить итоговую сумму заказа. Обратитесь к админу", "error");
            }
        });
    }

    function addComment()
    {
        var id_order = $('input[name="id_order"]').val();
        var comment = $('textarea[name="comment"]').val();

        $.ajax({
            type: "POST",
            url: 'work/update_order_info.php',
            data: {"id_order" : id_order, "type" : "add_comment", "comment" : comment},
            beforeSend: function()
            {
                $("#loading_add_comment").fadeIn(400);
            },
            success: function(resultdata)
            {
                $("#loading_add_comment").fadeOut(400);

                resultauthdata=jQuery.parseJSON(resultdata);

                if(resultauthdata.status=='ok')
                {
                    $('#result_add_comment').fadeIn(400);

                    setTimeout(function(){
                        window.location.reload();
                    }, 1000);
                }
                else
                {
                    swal("Ошибка", "Не удалось добавить комментарий - ошибка на сервере. Обратитесь к админу", "error");
                }
            },
            error: function()
            {
                $("#loading_add_comment").fadeOut(400);
                swal("Ошибка", "Не удалось добавить комментарий. Обратитесь к админу", "error");
            }
        });
    }

    function addSpecialistForOrder(id_specialist)
    {
        var id_order = $('input[name="id_order"]').val();

        $.ajax({
            type: "POST",
            url: 'work/update_order_info.php',
            data: {"id_order" : id_order, "type" : "add_specialist", "id_specialist" : id_specialist},
            beforeSend: function()
            {
                $("#loading_add_specialist").fadeIn(400);
            },
            success: function(resultdata)
            {
                $("#loading_add_specialist").fadeOut(400);

                resultauthdata=jQuery.parseJSON(resultdata);

                if(resultauthdata.status=='ok')
                {
                    $('#result_add_specialist').fadeIn(400);

                    window.location.reload();

                }
                else
                {
                    swal("Ошибка", "Не удалось добавить исполнителя - ошибка на сервере. Обратитесь к админу", "error");
                }
            },
            error: function()
            {
                $("#loading_add_specialist").fadeOut(400);
                swal("Ошибка", "Не удалось добавить исполнителя. Обратитесь к админу", "error");
            }
        });
    }

    function delSpecialistForOrder(id_specialist)
    {
        var id_order = $('input[name="id_order"]').val();

        var cause_cut = $('#cause_cut').val();
        var comment_cut = $('textarea[name="comment_cut"]').val();

        var ok_submit = false;

        if(comment_cut=='' || comment_cut==null)
        {
            $('textarea[name="comment_cut"]').removeClass();
            $('textarea[name="comment_cut"]').addClass('input-error');

            ok_submit = false;
        }
        else
        {
            $('textarea[name="comment_cut"]').removeClass();
            $('textarea[name="comment_cut"]').addClass('input-success');

            ok_submit = true;
        }

        if(ok_submit)
        {
            $.ajax({
                type: "POST",
                url: 'work/update_order_info.php',
                data: {
                    "id_order": id_order,
                    "type": "del_specialist",
                    "id_specialist": id_specialist,
                    "cause_cut": cause_cut,
                    "comment_cut": comment_cut
                },
                beforeSend: function () {
                    $("#loading_del_specialist").fadeIn(400);
                },
                success: function (resultdata) {
                    $("#loading_del_specialist").fadeOut(400);

                    resultauthdata = jQuery.parseJSON(resultdata);

                    if (resultauthdata.status == 'ok') {
                        $('#result_del_specialist').fadeIn(400);

                        window.location.reload();
                    }
                    else {
                        swal("Ошибка", "Не удалось снять исполнителя - ошибка на сервере. Обратитесь к админу", "error");
                    }
                },
                error: function () {
                    $("#loading_del_specialist").fadeOut(400);
                    swal("Ошибка", "Не удалось снять исполнителя. Обратитесь к админу", "error");
                }
            });
        }
    }

    function sendSmsWithContacts()
    {
        var id_order = $('input[name="id_order"]').val();

        $.ajax({
            type: "POST",
            url: 'work/send_contacts_for_specialist.php',
            data: {
                "id_order": id_order,
                "type": "sms"
            },
            beforeSend: function () {
                $("#loading_send_contacts").fadeIn(400);
                $("#pending_send_contacts").fadeIn(400);
            },
            success: function (resultdata)
            {
                $("#loading_send_contacts").fadeOut(400);
                $("#pending_send_contacts").fadeOut(400);

                resultauthdata = jQuery.parseJSON(resultdata);

                if (resultauthdata.status == 'ok')
                {
                    $('#result_send_contacts').text('Смс доставлено! Ожидаем обратной связи от исполнителя');
                    $('#result_send_contacts').fadeIn(400);

                    setTimeout(function(){
                        window.location.reload();
                    }, 1000);
                }
                else if(resultauthdata.status == 'error_not_add_event')
                {
                    swal("Внимание", "Смс доставлено, но не удалось зафиксировать событие обратной связи. Обновите страницу и нажмите что передали контакты самостоятельно. Обратитесь к админу", "warrning");
                }
                else if(resultauthdata.status == 'error_not_100')
                {
                    $('#result_send_contacts').text('Смс НЕ доставлено и даже не отправляется! Передавайте контакты самостоятельно');
                    $("#result_send_contacts").fadeIn(400);
                }
                else if(resultauthdata.status == 'error_not_103')
                {
                    $('#result_send_contacts').text('Смс НЕ доставлено! Передавайте контакты самостоятельно');
                    $("#result_send_contacts").fadeIn(400);
                }
                else
                {
                    swal("Ошибка", "Не удалось отправить смс - ошибка на сервере. Обратитесь к админу", "error");
                }
            },
            error: function () {
                $("#loading_send_contacts").fadeOut(400);
                $("#pending_send_contacts").fadeOut(400);
                swal("Ошибка", "Не удалось отправить смс. Обратитесь к админу", "error");
            }
        });
    }

    function sendSoloContacts()
    {
        var id_order = $('input[name="id_order"]').val();

        $.ajax({
            type: "POST",
            url: 'work/send_contacts_for_specialist.php',
            data: {
                "id_order": id_order,
                "type": "solo"
            },
            beforeSend: function () {
                $("#loading_send_contacts").fadeIn(400);
            },
            success: function (resultdata)
            {
                $("#loading_send_contacts").fadeOut(400);

                resultauthdata = jQuery.parseJSON(resultdata);

                if (resultauthdata.status == 'ok')
                {
                    $('#result_send_contacts').text('Передача данных зафиксирована!');
                    $('#result_send_contacts').fadeIn(400);

                    setTimeout(function(){
                        window.location.reload();
                    }, 1000);
                }
                else
                {
                    swal("Ошибка", "Не удалось зафиксировать передачу контактных данных - ошибка на сервере. Обратитесь к админу", "error");
                }
            },
            error: function () {
                $("#loading_send_contacts").fadeOut(400);
                swal("Ошибка", "Не удалось зафиксировать передачу контактных данных. Обратитесь к админу", "error");
            }
        });
    }

    function showUpdateStatus()
    {
        var id_order = $('input[name="id_order"]').val();

        $('#us_t_id_order').text(id_order);

        $('#update_status').modal('show');
    }

    function showUpdateEstimatedPrice()
    {
        $('#update_estimated_price').modal('show');
    }

    function showUpdateCalculatedPrice()
    {
        $('#update_calculated_price').modal('show');
    }

    function showUpdateFinishPrice()
    {
        $('#update_finish_price').modal('show');
    }

    function showAddComment()
    {
        $('#add_comment').modal('show');
    }

    function showAddSpecialist()
    {
        $('#add_specialist').modal('show');
    }

    function showDelSpecialist()
    {
        $('#del_specialist').modal('show');
    }

    function showSendContacts()
    {
        $('#send_contacts').modal('show');
    }

    function showAddEventCallbackASC()
    {
        $('#add_event_callback_asc').modal('show');
    }
</script>

<?
    if($_GET['add_spec_from_list']==1 && $order_info['status']=='Принят в обработку')
    {
        ?>
        <script>
            $(document).ready(function() {

                var id_specialist = '<?=$_GET['id_specialist']?>';

                $('#add_specialist').modal('show');

                $('#add_specialist').on('shown.bs.modal', function () {
                    $('#spec_'+id_specialist).addClass('indicator-work');

                    var destination = $('#spec_'+id_specialist).position().top;

                    $("#add_specialist").animate({scrollTop: destination+135}, 500);
                });
            });
        </script>
        <?
    }
?>

</body>
</html>