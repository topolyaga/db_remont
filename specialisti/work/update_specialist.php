<?
session_start();
header("Content-Type:text/html;charset=UTF-8");

require_once("../../inc/core/connectdb.php");
require_once("../../inc/core/user.php");
require_once("../../inc/core/events_for_specialist.php");
require_once("../../inc/core/add_rating_for_specialist.php");

if(!$user->isAuth())
{
    header('Location: http://'.$_SERVER['HTTP_HOST'].'/login.php');
    die();
}

$user_info = $user->GetInfo();

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
{
    $resultarray=array();

    $id_specialist = $_POST['id_specialist'];
    $type = $_POST['type'];

    //Достаем текущие данные
    $stmt=$db->prepare("SELECT `name`, `lastname`, `status` FROM `specialist_db` WHERE id=:id_specialist");
    $stmt->execute(array("id_specialist"=>$id_specialist));
    $now_spec_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if($type == 'event')
    {
        $tmp_event_type = explode("-", $_POST['event_type']);
        $id_ansubject = $_POST['id_ansubject'];
        $user_comment = $_POST['comment'];

        $rating = $_POST['rating'];
        $id_order = $_POST['id_order'];

        if($id_ansubject == '')
        {
            $id_ansubject = 0;
        }

        $event_type = $tmp_event_type[0];
        $event_sub_type = $tmp_event_type[1];

        $comment = '';

        switch($user_info['type'])
        {
            case 'manager': $comment = 'Менеджер'; break;
            case 'admin': $comment = 'Администратор'; break;
        }

        $comment.=' '.$user_info['name'].' '.$user_info['lastname'].' добавил событие для специалиста '.$now_spec_info['name'].' '.$now_spec_info['lastname'].'

        Событие: ';

        switch($event_type)
        {
            case 'callback': $comment.= 'Обратная связь'; break;
            case 'company': $comment.= 'Взаимодействие с компанией'; break;
            case 'comment': $comment.= 'Комментарий'; break;
        }

        switch($event_sub_type)
        {
            case 'communication': $comment.= ' [Выход на связь]'; break;
            case 'org_info': $comment.= ' [Организационные вопросы]'; break;

            case 'long_time_pay': $comment.= ' [Задержка выплаты комиссии]'; break;
            case 'no_pay': $comment.= ' [Невыплата комиссии]'; break;

            default: $event_sub_type = ''; break;
        }

        //Проверям если событие по взаимодействию с компанией
        if($event_type == 'company')
        {
            /*Значит обязательно должно быть поле с id заказа,
            тут нам нужно проверить вообще был ли специалист причастен
            к заказу, тут смотрим события по заказу (смотрим spec_work, так как он отражает что
            специалист начинал работу по заказу)
            */

            $stmt=$db->prepare("SELECT id FROM `events` WHERE type='order' && sub_type='spec_work' && id_ansubject=:id_order && dop_id_subject_1=:id_specialist LIMIT 0,1");
            $stmt->execute(array("id_order"=>$id_order, "id_specialist"=>$id_specialist));
            $info_order_for_spec = $stmt->fetch(PDO::FETCH_ASSOC);

            if($info_order_for_spec['id']=='')
            {
                //Специалист не имеет отношение к заказу, даем ответ и завершаем скрипт
                $resultarray = array("status"=>"error_check", "text"=>"Специалист не имеет отношения к этому заказу. Провете ID заказа");

                //Возращаем ответ
                echo json_encode($resultarray);
                die();
            }

            $id_ansubject = $id_order;

            //Если все ок, то добавим к коменту id заказа за одно
            $comment.='

            ID заказа: '.$id_order;
        }

        $comment.='

        Комментарий: '.$user_comment;

        if($rating!='none')
        {
            $comment.='

            Оценка в рейтинг: '.$rating;
        }

        if(sendEventForSpecialist($id_specialist, $event_type, $event_sub_type, $_SESSION["user_id"], $comment, $id_ansubject))
        {
            if($rating!='none')
            {
                //пишем значение в рейтинг
                switch($event_type)
                {
                    case 'callback': $type = 'callback'; break;
                    case 'company': $type = 'income'; break;
                }

                addRatingValueForSpecialist($id_specialist, $type, $rating);
            }

            $resultarray = array("status"=>"ok");
        }
        else
        {
            $resultarray = array("status"=>"error");
        }



    }

    if($type == 'status')
    {
        $status = $_POST['status'];

        $stmt=$db->prepare("UPDATE `specialist_db` SET status=:status WHERE id=:id_specialist");

        $stmt->execute(array(
            "id_specialist"=>$id_specialist,
            "status"=>$status
        ));

        if($stmt)
        {
            //Все ок стутус изменили, пишем событие
            $comment = '';

            switch($user_info['type'])
            {
                case 'manager': $comment = 'Менеджер'; break;
                case 'admin': $comment = 'Администратор'; break;
            }

            $comment.=' '.$user_info['name'].' '.$user_info['lastname'].' сменил статус специалиста '.$now_spec_info['name'].' '.$now_spec_info['lastname'].' с "'.$now_spec_info['status'].'" на "'.$status.'"';


            sendEventForSpecialist($id_specialist, 'update_status', '', $_SESSION["user_id"], $comment);


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
    echo 'error';
    die();
}
?>