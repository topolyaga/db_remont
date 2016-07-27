<?php
session_start();
header("Content-Type:text/html;charset=UTF-8");

require_once("../../inc/core/connectdb.php");
require_once("../../inc/core/user.php");
require("../../inc/core/events.php");
require("../../inc/core/events_for_specialist.php");
require("../../inc/core/add_rating_for_specialist.php");
require("../../inc/core/send_sms.php");

if(!$user->isAuth())
{
    header('Location: http://'.$_SERVER['HTTP_HOST'].'/login.php');
    die();
}

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
{
    $id_order = $_POST['id_order'];
    $type = $_POST['type'];

    //Достаем информацию о заказе
    $stmt=$db->prepare("SELECT client_name, client_phone, id_specialist, status FROM `orders` WHERE id=:id_order");
    $stmt->execute(array("id_order"=>$id_order));
    $info_order = $stmt->fetch(PDO::FETCH_ASSOC);

    //Берем контактные данные исполнителя
    $stmt=$db->prepare("SELECT name, lastname, phone FROM `specialist_db` WHERE id=:id_specialist");
    $stmt->execute(array("id_specialist"=>$info_order['id_specialist']));
    $info_specialist = $stmt->fetch(PDO::FETCH_ASSOC);

    $resultarray=array();

    if($type=='sms')
    {
        $specialist_phone = preg_replace('/[^0-9]/', '', $info_specialist['phone']);

        $text_sms = $info_order['client_name']." ".$info_order['client_phone'];

        $result = SendSms($specialist_phone, $text_sms);

        $lines = explode("\n", $result);

        $status = $lines[0];
        $id_sms_smsru = $lines[1];

        if($status=='100')
        {
            /*  Статус сообщение принято к отправке, далее ждем статуса доставленно
            не будем сто раз обращаться сделаем 3 обращения через разные промежутки времени -
            выделил в функцию в send_sms.php    */

            $now_status = f_GetSmsStatus($id_sms_smsru);

            if($now_status==103)
            {
                //Все смс доставлено, теперь пишим события
                $id_specialist = $info_order['id_specialist'];

                //Достаем информацию о пользователе
                $user_info = $user->GetInfo();

                switch($user_info['type'])
                {
                    case 'manager': $comment = 'Менеджер'; break;
                    case 'admin': $comment = 'Администратор'; break;
                }

                $comment.= ' '.$user_info['name'].' '.$user_info['lastname'].' отправил, автоматическим смс сообщением, контакты заказчика исполнителю '.$info_specialist['name'].' '.$info_specialist['lastname'].' по заказу #'.$id_order.'';

                if(goEvent('order', 'send_contacts', $_SESSION["user_id"], $id_order, 'Назначен исполнитель', $comment, $id_specialist))
                {
                    //Теперь фиксируем событие для спеца
                    $comment = '';

                    switch($user_info['type'])
                    {
                        case 'manager': $comment = 'Менеджер'; break;
                        case 'admin': $comment = 'Администратор'; break;
                    }

                    $comment.=' '.$user_info['name'].' '.$user_info['lastname'].' передал исполнителю '.$info_specialist['name'].' '.$info_specialist['lastname'].' контактные данные заказчика по заказу #'.$id_order.'

                    Событие: Заказ [Передача контактных данных]

                    ID заказа: '.$id_order;

                    sendEventForSpecialist($id_specialist, 'order', 'send_contacts', $_SESSION["user_id"], $comment, $id_order);

                    $resultarray = array("status"=>"ok");
                }
                else
                {
                    $resultarray = array("status"=>"error_not_add_event");
                }
            }
            else
            {
                $resultarray = array("status"=>"error_not_103");
            }
        }
        else
        {
            $resultarray = array("status"=>"error_not_100");
        }
    }

    if($type=='solo')
    {
        $id_specialist = $info_order['id_specialist'];

        //Достаем информацию о пользователе
        $user_info = $user->GetInfo();

        switch($user_info['type'])
        {
            case 'manager': $comment = 'Менеджер'; break;
            case 'admin': $comment = 'Администратор'; break;
        }

        $comment.= ' '.$user_info['name'].' '.$user_info['lastname'].' отправил, самостоятельно, контакты заказчика исполнителю '.$info_specialist['name'].' '.$info_specialist['lastname'].' по заказу #'.$id_order.'';

        if(goEvent('order', 'send_contacts', $_SESSION["user_id"], $id_order, 'Назначен исполнитель', $comment, $id_specialist))
        {
            //Теперь фиксируем событие для спеца
            $comment = '';

            switch($user_info['type'])
            {
                case 'manager': $comment = 'Менеджер'; break;
                case 'admin': $comment = 'Администратор'; break;
            }

            $comment.=' '.$user_info['name'].' '.$user_info['lastname'].' передал исполнителю '.$info_specialist['name'].' '.$info_specialist['lastname'].' контактные данные заказчика по заказу #'.$id_order.'

                    Событие: Заказ [Передача контактных данных]

                    ID заказа: '.$id_order;

            sendEventForSpecialist($id_specialist, 'order', 'send_contacts', $_SESSION["user_id"], $comment, $id_order);

            $resultarray = array("status"=>"ok");
        }
        else
        {
            $resultarray = array("status"=>"error");
        }
    }

    //Возращаем ответ
    echo json_encode($resultarray);
    die();
}
else
{
    header('Location: http://'.$_SERVER['HTTP_HOST']);
    die();
}
?>