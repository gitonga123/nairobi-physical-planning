<?php
/**
 * _comments_conditions template.
 *
 * Shows summary of conditions from all reviewers
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
/*$q = Doctrine_Query::create()
    ->from('CfUser a')
    ->where('a.nid = ?', $sf_user->getAttribute('userid'));
$reviewer = $q->fetchOne();*/

$q = Doctrine_Query::create()
    ->from('Permits a')
    ->where('a.applicationform = ?', $application->getFormId());
$permit = $q->fetchOne();

if($permit)
{
?>
<div class="table-responsive">

  <table class="table dt-on-steroids mb0" id="table3">
    <thead>
      <tr>
        <th>#</th>
        <th style="min-width: 300px;">Description</th>
        <th style="width: 150px;">Selected?</th>
      </tr>
    </thead>
    <tbody>
<?php
    $q = Doctrine_Query::create()
        ->from('ConditionsOfApproval a')
        ->where('a.permit_id = ?', $permit->getId())
		//OTB ADD
		->orWhere('a.entry_id = ?',$application->getId())
		//OTB END
        ->orderBy('a.short_name ASC');
    $conditions = $q->execute();

    foreach($conditions as $condition)
    {
        $q = Doctrine_Query::create()
            ->from('ApprovalCondition a')
            ->where('a.entry_id = ?', $application->getId())
            ->andWhere('a.condition_id = ?', $condition->getId());
        $cnd = $q->fetchOne();

        $resolved = "";
        if(empty($cnd)) {

            $resolved = $resolved."<span class='glyphicon glyphicon-remove'></span>";
        }
        else
        {
            
            $resolved = $resolved."<span class='glyphicon glyphicon-ok'></span>";
        }
?>
      <tr>
<!--OTB Patch - Start: Parse conditions of approval when viewing application-->
<?php
		$templateparser = new Templateparser();
		$cd_short = $condition->getShortName();
		$cd_desc = $condition->getDescription();
		$parsed=$templateparser->getCommentSheetDetails($application->getId());
		if(count($parsed)):
			foreach($parsed as $key=>$comment_element){
				$cd_short = str_replace('{'.$key.'}', $comment_element, $cd_short);
				$cd_desc = str_replace('{'.$key.'}', $comment_element, $cd_desc);
			}
		else:
			//Break down the tag
			$fst_occ=stripos($cd_desc,'{fm_c');
			//error_log('--------DESC---'.$cd_desc);
			//error_log('--------{fm_c position---'.$fst_occ);
			if($fst_occ !== false){
				$cond_tag = substr($cd_desc,$fst_occ,((stripos($cd_desc,'}')-$fst_occ)+1));
				//error_log('-----------Tag--'.$cond_tag);
				//breakdown the tag
				$tag_arr=explode('_',$cond_tag);
				//error_log(print_r($tag_arr,true));
				//comment tag get default
				$tag_form=str_ireplace('c','',$tag_arr[1]);
				$tag_elem=$tag_arr[3];
				//Query default value
				$elemts = Doctrine_Query::create()->getConnection()->execute (
					"SELECT element_default_value FROM ap_form_elements e WHERE e.form_id = :form_id AND e.element_id = :element_id", 
					array('form_id'=>$tag_form,'element_id' => $tag_elem)
				);

				while ( $ele = $elemts->fetch(\PDO::FETCH_ASSOC) ) {
					$cd_desc = $ele['element_default_value'];
				}
			}
		endif;
?>
        <td><?php echo $cd_short; ?></td>
        <td><?php echo $cd_desc; ?></td>
<!--OTB Patch - End: Parse conditions of approval when viewing application-->
        <!--<td><?php echo $condition->getShortName() ?></td>
        <td><?php echo $condition->getDescription() ?></td>-->
        <td><div id="cn_<?php echo $condition->getId() ?>"><?php echo $resolved ?></div></td>
      </tr>
<?php
    }
?>
    </tbody>
  </table>
</div>
<?php
}
?>
