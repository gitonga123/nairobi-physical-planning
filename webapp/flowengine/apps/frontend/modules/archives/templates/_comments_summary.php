<?php
/**
 * _comments_summary template.
 *
 * Shows summary of comments from all reviewers
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
?>
<table class="table mb0">
    <thead>
    <tr><th>#</th><th>Description</th><th>Comment</th></tr>
    </thead>
    <tbody>
<?php

$dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
mysql_select_db(sfConfig::get('app_mysql_db'),$dbconn);

$count = 0;

$q = Doctrine_Query::create()
    ->from("Task a")
    ->where("a.application_id = ?", $application->getId());
$tasks = $q->execute();

foreach($tasks as $task)
{
    $q = Doctrine_Query::create()
        ->from("TaskForms a")
        ->where("a.task_id = ?", $task->getId());
    $taskforms = $q->execute();
    foreach($taskforms as $taskform)
    {
        $sql = "SELECT * FROM ap_form_".$taskform->getFormId()." WHERE id = ".$taskform->getEntryId();
        $results = mysql_query($sql, $dbconn);
        while($row = mysql_fetch_assoc($results))
        {
            $q = Doctrine_Query::create()
                ->from("ApFormElements a")
                ->where("a.form_id = ?", $taskform->getFormId())
                ->orderBy("a.element_position ASC");
            $elements = $q->execute();
            foreach($elements as $element)
            {

                if($element->getElementType() == "radio")
                {
                    $fields = mysql_list_fields(sfConfig::get('app_mysql_db'), "ap_form_".$taskform->getFormId());
                    $columns = mysql_num_fields($fields);
                    for ($i = 0; $i < $columns; $i++) {$field_array[] = mysql_field_name($fields, $i);}

                    if (in_array('element_'.$element->getElementId().'_other', $field_array) && $row['element_'.$element->getElementId().'_other'])
                    {
                        $count++;
                        echo "<tr><td>".$count."</td><td>".$element->getElementTitle()."</td><td>".$row['element_'.$element->getElementId().'_other']."</td></tr>";
                    }
                }
            }
        }
    }
}


$comment_count = 0;
$q = Doctrine_Query::create()
   ->from('CfFormslot a');
$slots = $q->execute();
foreach($slots as $slot)
{
	$q = Doctrine_Query::create()
	   ->from('Comments a')
	   ->where('a.circulation_id = ?', $application->getCirculationId())
	   ->andWhere('a.slot_id = ?', $slot->getNid());
	$comments = $q->execute();

	if(sizeof($comments) > 0)
	{
		$comment_count++;
	?>
				<?php
				foreach($comments as $comment)
				{
					$q = Doctrine_Query::create()
					   ->from('CfInputfield a')
					   ->where('a.nid = ?', $comment->getFieldId());
					$field = $q->fetchOne();

                    $resolved = "";
                    if($comment->getFormId() == "0")
                    {
                         $resolved = $resolved."<span class='glyphicon glyphicon-remove'></span> Not Resolved."; 
                    }
                    else
                    {
                         $resolved = $resolved."<span class='glyphicon glyphicon-ok'></span> Resolved."; 
                    }

					echo "<tr><td>".$count."</td><td>".$field->getStrname()."</td><td>".$comment->getComment()."</td></tr>";

					$count++;

				}
	}
}

    if($count <= 0)
    {
        echo "
			<tr>
			<td><i class=\"bold-label\">No records found</i></td>
			</tr>
		";
    }

?>
    </tbody>
</table>