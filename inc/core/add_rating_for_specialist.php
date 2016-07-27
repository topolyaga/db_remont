<?
function addRatingValueForSpecialist($id_specialist, $type, $value)
{
    global $db;

    $stmt=$db->prepare("INSERT INTO `specialist_rating`(`add_date`, `id_specialist`, `type`, `value`) VALUES (NOW(),:id_specialist, :type, :value)");

    $stmt->execute(array(
        "id_specialist"=>$id_specialist,
        "type"=>$type,
        "value"=>$value
        ));

    if($stmt)
    {
        return true;
    }
    else
    {
        return false;
    }
}
?>