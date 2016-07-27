<?php
session_start();
header("Content-Type:text/html;charset=UTF-8");

require_once("../../inc/core/connectdb.php");
require_once("../../inc/core/user.php");
require("../../inc/core/events.php");
require("../../inc/core/events_for_specialist.php");
require("../../inc/core/add_rating_for_specialist.php");

if(!$user->isAuth())
{
    header('Location: http://'.$_SERVER['HTTP_HOST'].'/login.php');
    die();
}

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
{
    $id_order = $_POST['id_order'];
    $type = $_POST['type'];

    //Достаем текущий статус
    $stmt=$db->prepare("SELECT id_specialist, status FROM `orders` WHERE id=:id_order");
    $stmt->execute(array("id_order"=>$id_order));
    $info_order = $stmt->fetch(PDO::FETCH_ASSOC);

    $resultarray=array();

    if($type=='estimated_price')
    {
        $estimated_price = $_POST['estimated_price'];

        $stmt=$db->prepare("UPDATE `orders` SET `estimated_price`=:estimated_price WHERE id=:id_order");
        $stmt->execute(array("id_order"=>$id_order, "estimated_price"=>$estimated_price));

        if($stmt)
        {
            //Достаем информацию о пользователе
            $user_info = $user->GetInfo();

            switch($user_info['type'])
            {
                case 'manager': $comment = 'Менеджер'; break;
                case 'admin': $comment = 'Администратор'; break;
            }

            $comment.= ' '.$user_info['name'].' '.$user_info['lastname'].' обновил ориентировочную сумму заказа #'.$id_order.'

            Установлена сумма: '.number_format($estimated_price, 0, ".", " ").' руб';

            goEvent('order', 'update_info', $_SESSION["user_id"], $id_order, $info_order['status'], $comment);

            $resultarray = array("status"=>"ok");
        }
        else
        {
            $resultarray = array("status"=>"error");
        }
    }

    if($type=='calculated_price')
    {
        $calculated_price = $_POST['calculated_price'];

        /*Обновляется рассчитаная сумма, значит нужно проверить назначен ли вообще специалист
        и если да то проверяем давал ли он обратную связь
        */

        if($info_order['id_specialist']!=0)
        {
            //Ок теперь смотрим события обратной связи
            $stmt=$db->prepare("SELECT id FROM `events` WHERE type='order' && sub_type='callback_spec' && id_ansubject=:id_order && now_status='Осмотр объекта' LIMIT 0,1");
            $stmt->execute(array("id_order"=>$id_order));
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if($event['id']=='' || $event['id']==null)
            {
                $resultarray = array("status"=>"error_no_callback_spec", "text"=>"Невозможно изменить рассчитаную сумму, так как исполнитель не дал обратную связь по осмотру объекта, или Вы не зафиксировали событие обратной связи!");

                //Возращаем ответ
                echo json_encode($resultarray);
                die();
            }
        }
        else
        {
            $resultarray = array("status"=>"error_no_spec", "text"=>"Невозможно изменить рассчитаную сумму, так как не назначен исполнитель для заказа!");

            //Возращаем ответ
            echo json_encode($resultarray);
            die();
        }

        $stmt=$db->prepare("UPDATE `orders` SET `calculated_price`=:calculated_price WHERE id=:id_order");
        $stmt->execute(array("id_order"=>$id_order, "calculated_price"=>$calculated_price));

        if($stmt)
        {
            //Достаем информацию о пользователе
            $user_info = $user->GetInfo();

            switch($user_info['type'])
            {
                case 'manager': $comment = 'Менеджер'; break;
                case 'admin': $comment = 'Администратор'; break;
            }

            $comment.= ' '.$user_info['name'].' '.$user_info['lastname'].' обновил рассчитаную сумму заказа #'.$id_order.'

            Установлена сумма: '.number_format($calculated_price, 0, ".", " ").' руб';

            goEvent('order', 'update_info', $_SESSION["user_id"], $id_order, $info_order['status'], $comment);

            $resultarray = array("status"=>"ok");
        }
        else
        {
            $resultarray = array("status"=>"error");
        }
    }

    if($type=='finish_price')
    {
        $finish_price = $_POST['finish_price'];

        /*Обновляется итоговая сумма, значит нужно проверить назначен ли вообще специалист
        и если да то проверяем выполнил ли он работы
        */

        if($info_order['id_specialist']!=0)
        {
            //Ок теперь смотрим события обратной связи
            $stmt=$db->prepare("SELECT id FROM `events` WHERE type='order' && sub_type='spec_end_work' && id_ansubject=:id_order && now_status='Исполнен' LIMIT 0,1");
            $stmt->execute(array("id_order"=>$id_order));
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if($event['id']=='' || $event['id']==null)
            {
                $resultarray = array("status"=>"error_no_spec_end_work", "text"=>"Невозможно изменить итоговую сумму, так как исполнитель не закончил работы на объекте, или Вы не зафиксировали событие исполнения работ!");

                //Возращаем ответ
                echo json_encode($resultarray);
                die();
            }
        }
        else
        {
            $resultarray = array("status"=>"error_no_spec", "text"=>"Невозможно изменить итоговую сумму, так как не назначен исполнитель для заказа!");

            //Возращаем ответ
            echo json_encode($resultarray);
            die();
        }

        $stmt=$db->prepare("UPDATE `orders` SET `finish_price`=:finish_price WHERE id=:id_order");
        $stmt->execute(array("id_order"=>$id_order, "finish_price"=>$finish_price));

        if($stmt)
        {
            //Достаем информацию о пользователе
            $user_info = $user->GetInfo();

            switch($user_info['type'])
            {
                case 'manager': $comment = 'Менеджер'; break;
                case 'admin': $comment = 'Администратор'; break;
            }

            $comment.= ' '.$user_info['name'].' '.$user_info['lastname'].' обновил итоговую сумму заказа #'.$id_order.'

            Установлена сумма: '.number_format($finish_price, 0, ".", " ").' руб';

            goEvent('order', 'update_info', $_SESSION["user_id"], $id_order, $info_order['status'], $comment);

            $resultarray = array("status"=>"ok");
        }
        else
        {
            $resultarray = array("status"=>"error");
        }
    }

    if($type=='add_comment')
    {
        $comment_user = $_POST['comment'];

        //Достаем информацию о пользователе
        $user_info = $user->GetInfo();

        switch($user_info['type'])
        {
            case 'manager': $comment = 'Менеджер'; break;
            case 'admin': $comment = 'Администратор'; break;
        }

        $comment.= ' '.$user_info['name'].' '.$user_info['lastname'].' добавил комментарий к заказу #'.$id_order.'

            Комментарий: '.$comment_user.'';

        if(goEvent('order', 'comment', $_SESSION["user_id"], $id_order, $info_order['status'], $comment))
        {
            $resultarray = array("status"=>"ok");
        }
        else
        {
            $resultarray = array("status"=>"error");
        }
    }

    if($type=='add_specialist')
    {
        $id_specialist = $_POST['id_specialist'];

        if($info_order['id_specialist']==0)
        {
            //Все ок спеца еще нет
            $stmt=$db->prepare("UPDATE `orders` SET `id_specialist`=:id_specialist, `status`='Назначен исполнитель' WHERE id=:id_order");
            $stmt->execute(array("id_order"=>$id_order, "id_specialist"=>$id_specialist));

            if($stmt)
            {
                //Достаем текущие данные
                $stmt=$db->prepare("SELECT `name`, `lastname` FROM `specialist_db` WHERE id=:id_specialist");
                $stmt->execute(array("id_specialist"=>$id_specialist));
                $spec_info = $stmt->fetch(PDO::FETCH_ASSOC);

                //Достаем информацию о пользователе
                $user_info = $user->GetInfo();

                switch($user_info['type'])
                {
                    case 'manager': $comment = 'Менеджер'; break;
                    case 'admin': $comment = 'Администратор'; break;
                }

                $comment.= ' '.$user_info['name'].' '.$user_info['lastname'].' назначил исполнителя '.$spec_info['name'].' '.$spec_info['lastname'].' для заказа #'.$id_order.'

                Заказу присвоен статус: Назначен исполнитель';

                if(goEvent('order', 'spec_add', $_SESSION["user_id"], $id_order, 'Назначен исполнитель', $comment, $id_specialist))
                {
                    //Теперь фиксируем событие для спеца
                    $comment = '';

                    switch($user_info['type'])
                    {
                        case 'manager': $comment = 'Менеджер'; break;
                        case 'admin': $comment = 'Администратор'; break;
                    }

                    $comment.=' '.$user_info['name'].' '.$user_info['lastname'].' назначил специалиста '.$now_spec_info['name'].' '.$now_spec_info['lastname'].' исполнителем заказа #'.$id_order.'

                    Событие: Заказ [Назначение исполнителем]

                    ID заказа: '.$id_order;


                    sendEventForSpecialist($id_specialist, 'order', 'appointment', $_SESSION["user_id"], $comment, $id_order);

                    $resultarray = array("status"=>"ok");
                }
                else
                {
                    $resultarray = array("status"=>"error");
                }
            }
        }
        else
        {
            $resultarray = array("status"=>"error_have_spec", "text"=>"Невозможно назначить специалиста исполнителем, так как в данный момент назначен другой исполнитель для заказа!");
        }
    }


    if($type=='del_specialist')
    {
        $id_specialist = $_POST['id_specialist'];

        $cause_cut = $_POST['cause_cut'];
        $comment_cut = $_POST['comment_cut'];

        //Обновляем
        $stmt=$db->prepare("UPDATE `orders` SET `id_specialist`='0', `status`='Принят в обработку' WHERE id=:id_order");
        $stmt->execute(array("id_order"=>$id_order));

        if($stmt)
        {
            //Достаем текущие данные
            $stmt=$db->prepare("SELECT `name`, `lastname` FROM `specialist_db` WHERE id=:id_specialist");
            $stmt->execute(array("id_specialist"=>$id_specialist));
            $spec_info = $stmt->fetch(PDO::FETCH_ASSOC);

            //Достаем информацию о пользователе
            $user_info = $user->GetInfo();

            switch($user_info['type'])
            {
                case 'manager': $comment = 'Менеджер'; break;
                case 'admin': $comment = 'Администратор'; break;
            }

            $comment.= ' '.$user_info['name'].' '.$user_info['lastname'].' снял исполнителя '.$spec_info['name'].' '.$spec_info['lastname'].' с заказа #'.$id_order.'

            Степень доставленных неудобств: '.$cause_cut.'

            Причина снятия с заказа: '.$comment_cut.'

            Заказу присвоен статус: Принят в обработку';

            if(goEvent('order', 'spec_del', $_SESSION["user_id"], $id_order, 'Принят в обработку', $comment, $id_specialist))
            {
                //Теперь фиксируем событие для спеца
                $comment = '';

                $rating = 0;

                switch($cause_cut)
                {
                    case 'Нейтральная': $rating = 0; break;
                    case 'Негативная (низкий)': $rating = 3; break;
                    case 'Негативная (средний)': $rating = 2; break;
                    case 'Негативная (высокий)': $rating = 1; break;
                }

                if($rating!=0)
                {
                    //Степень негаивная, значит вносим значение в рейтинг
                    addRatingValueForSpecialist($id_specialist, 'close_order', $rating);
                }

                switch($user_info['type'])
                {
                    case 'manager': $comment = 'Менеджер'; break;
                    case 'admin': $comment = 'Администратор'; break;
                }

                if($rating==0)
                {
                    $rating = 'Оценка в рейтинг не заносится';
                }

                $comment.=' '.$user_info['name'].' '.$user_info['lastname'].' снял специалиста '.$now_spec_info['name'].' '.$now_spec_info['lastname'].' с заказа #'.$id_order.'

                Событие: Заказ [Снятие с заказа]

                Степень доставленных неудобств: '.$cause_cut.'

                Причина снятия с заказа: '.$comment_cut.'

                Оценка в рейтинг: '.$rating.'

                ID заказа: '.$id_order;

                sendEventForSpecialist($id_specialist, 'order', 'cut', $_SESSION["user_id"], $comment, $id_order);

                $resultarray = array("status"=>"ok");
            }
            else
            {
                $resultarray = array("status"=>"error");
            }
        }
        else
        {
            $resultarray = array("status"=>"error_have_spec", "text"=>"Невозможно назначить специалиста исполнителем, так как в данный момент назначен другой исполнитель для заказа!");
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