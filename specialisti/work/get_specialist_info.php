<?
session_start();
header("Content-Type:text/html;charset=UTF-8");

require_once("../../inc/core/connectdb.php");
require_once("../../inc/core/user.php");

if(!$user->isAuth())
{
    header('Location: http://'.$_SERVER['HTTP_HOST'].'/login.php');
    die();
}

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
{
    // Если запрос послан с xmlhttprequest, то есть это ajax запрос

    $id_specialist = $_POST['id_specialist'];

    $resultarray=array();

    //Достаем инфу о спеце
    $stmt=$db->prepare("SELECT `id`, DATE_FORMAT( add_date,  '%d.%m.%Y в %H:%i' ) as add_date, `name`, `lastname`, `avatar_link`, `id_categorys`, `phone`, `phone2`, `min_price`, `min_price_opt`, `min_price_for_order`, `experience`, `contract`, `guarantee`, `scale`, `areas`, `comment`, `status` FROM `specialist_db` WHERE id=:id_specialist");
    $stmt->execute(array("id_specialist"=>$id_specialist));
    $specialist_info=$stmt->fetch(PDO::FETCH_ASSOC);

    //Достаем все категории
    $stmt=$db->prepare("SELECT * FROM `specialist_categorys` WHERE 1");
    $stmt->execute();

    $category_titles = array();

    while($category=$stmt->fetch(PDO::FETCH_ASSOC))
    {

        $category_titles[$category['id']] = $category['title'];
    }

    //Преобразем id категорий в названия
    $ids_categorys = explode(",", $specialist_info['id_categorys']);

    $str_categorys = '';

    foreach($ids_categorys as $val)
    {
        $str_categorys.=$category_titles[$val].', ';
    }

    $str_categorys = substr($str_categorys, 0, -2);

    //Проверим если нет фото
    if($specialist_info['avatar_link']=='')
    {
        $specialist_info['avatar_link'] = 'http://baza-remontprofi.ru/img/no_photo.jpg';
    }

    //Функция преобразования 0 и 1 в да или нет
    function boolenForStr($value)
    {
        switch($value)
        {
            case 1: return 'Да'; break;
            case 0: return 'Нет'; break;
        }
    }

    //Районы
    if($specialist_info['areas']=='')
    {
        $specialist_info['areas'] = 'Все';
    }

    //Узнаем рейтинг
    $stmt=$db->prepare("SELECT SUM(value)/count(id) as rating FROM `specialist_rating` WHERE id_specialist=:id_specialist");
    $stmt->execute(array("id_specialist"=>$specialist_info['id']));
    $rating=$stmt->fetch(PDO::FETCH_ASSOC);

    $rating = round($rating['rating'], 2);

    if($specialist_info['comment']=='')
    {
        $specialist_info['comment'] = 'Комментария нет';
    }

    //Считаем статистику

    $statistic = array();

    //Узнаем кол-во назначений исполнителем (передано заказов)
    $stmt=$db->prepare("SELECT count(DISTINCT id_ansubject) as kolvo FROM `events` WHERE type='order' && sub_type='spec_add' && dop_id_subject_1=:id_specialist");
    $stmt->execute(array("id_specialist"=>$specialist_info['id']));
    $tmp=$stmt->fetch(PDO::FETCH_ASSOC);

    $statistic['count_spec_add'] = $tmp['kolvo'];

    //Узнаем кол-во осмотров исполнителем
    $stmt=$db->prepare("SELECT count(DISTINCT id_ansubject) as kolvo FROM `events` WHERE type='order' && sub_type='view_object' && dop_id_subject_1=:id_specialist");
    $stmt->execute(array("id_specialist"=>$specialist_info['id']));
    $tmp=$stmt->fetch(PDO::FETCH_ASSOC);

    $statistic['count_view_object'] = $tmp['kolvo'];

    //Узнаем кол-во начатых заказов
    $stmt=$db->prepare("SELECT count(DISTINCT id_ansubject) as kolvo FROM `events` WHERE type='order' && sub_type='spec_work' && dop_id_subject_1=:id_specialist");
    $stmt->execute(array("id_specialist"=>$specialist_info['id']));
    $tmp=$stmt->fetch(PDO::FETCH_ASSOC);

    $statistic['count_spec_work'] = $tmp['kolvo'];

    //Узнаем кол-во оконченых заказов, но не закрытых
    $stmt=$db->prepare("SELECT count(DISTINCT id_ansubject) as kolvo FROM `events` WHERE type='order' && sub_type='spec_end_work' && dop_id_subject_1=:id_specialist");
    $stmt->execute(array("id_specialist"=>$specialist_info['id']));
    $tmp=$stmt->fetch(PDO::FETCH_ASSOC);

    $statistic['count_spec_end_work'] = $tmp['kolvo'];

    //Узнаем кол-во закрытых-расчитаных заказов
    $stmt=$db->prepare("SELECT count(DISTINCT id_ansubject) as kolvo FROM `events` WHERE type='order' && sub_type='close' && dop_id_subject_1=:id_specialist");
    $stmt->execute(array("id_specialist"=>$specialist_info['id']));
    $tmp=$stmt->fetch(PDO::FETCH_ASSOC);

    $statistic['count_close'] = $tmp['kolvo'];

    //Узнаем кол-во отмененых заказов
    $stmt=$db->prepare("SELECT count(DISTINCT id_ansubject) as kolvo FROM `events` WHERE type='order' && sub_type='canceled' && dop_id_subject_1=:id_specialist");
    $stmt->execute(array("id_specialist"=>$specialist_info['id']));
    $tmp=$stmt->fetch(PDO::FETCH_ASSOC);

    $statistic['count_canceled'] = $tmp['kolvo'];

    //Кол-во не оплаченых, исполненные минус закрытые
    $statistic['count_not_pay'] = $statistic['count_spec_end_work']-$statistic['count_close'];


    //Достаем последние 10 событий
    $stmt=$db->prepare("SELECT id, DATE_FORMAT( add_date,  '%d.%m.%Y в %H:%i' ) as add_date, comment FROM `specialist_events` WHERE id_specialist=:id_specialist ORDER BY `specialist_events`.`id`  DESC LIMIT 0,10");
    $stmt->execute(array("id_specialist"=>$specialist_info['id']));

    $last_events = array();
    $i=0;

    while($tmp=$stmt->fetch(PDO::FETCH_ASSOC))
    {
        $last_events[$i] = array(
            "id"=>$tmp['id'],
            "add_date"=>$tmp['add_date'],
            "comment"=>nl2br($tmp['comment']),
        );
        $i++;
    }

    $resultarray = array(
        "status_sel"=>"ok",
        "id"=>$specialist_info['id'],
        "add_date"=>$specialist_info['add_date'],
        "name_lastname"=>$specialist_info['name'].' '.$specialist_info['lastname'],
        "id_name_lastname"=>'[ ID:'.$specialist_info['id'].' ] '.$specialist_info['name'].' '.$specialist_info['lastname'],
        "avatar_link"=>$specialist_info['avatar_link'],
        "phone"=>$specialist_info['phone'],
        "phone2"=>$specialist_info['phone2'],
        "min_price"=>$specialist_info['min_price'].' руб',
        "min_price_opt"=>$specialist_info['min_price_opt'].' руб',
        "min_price_for_order"=>$specialist_info['min_price_for_order'].' руб',
        "experience"=>date('Y')-$specialist_info['experience'].' лет',
        "contract"=>boolenForStr($specialist_info['contract']),
        "guarantee"=>boolenForStr($specialist_info['guarantee']),
        "scale"=>boolenForStr($specialist_info['scale']),
        "areas"=>$specialist_info['areas'],
        "comment"=>nl2br($specialist_info['comment']),
        "status"=>$specialist_info['status'],
        "categorys"=>$str_categorys,
        "rating"=>$rating,
        "count_spec_add"=>$statistic['count_spec_add'],
        "count_view_object"=>$statistic['count_view_object'],
        "count_spec_work"=>$statistic['count_spec_work'],
        "count_spec_end_work"=>$statistic['count_spec_end_work'],
        "count_close"=>$statistic['count_close'],
        "count_canceled"=>$statistic['count_canceled'],
        "count_not_pay"=>$statistic['count_not_pay'],
        "last_events"=>$last_events
    );


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