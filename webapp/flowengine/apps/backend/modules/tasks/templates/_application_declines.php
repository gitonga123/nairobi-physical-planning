<?php
/**
 * _comments_declines template.
 *
 * Shows summary of reasons for declines from all reviewers
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
$q = Doctrine_Query::create()
         ->from('EntryDecline a')
         ->where('a.entry_id = ?', $application->getId());
$declines = $q->execute();

//If application has been previously declined before, display the reasons why it was declined
if(sizeof($declines) > 0)
{
?>
<table class="table mb30">
	<thead>
		<tr><th>#</th><th>Description</th><th>Resolved?</th></tr>
	</thead>
	<tbody>
	<?php
    $count = 0;
	//Iterate through previous reasons of decline
    foreach($declines as $decline)
    {
        $resolved = "";
        if($decline->getResolved() == "0") //If the previous reason for decline has not been resolved
        {
             if($sf_user->mfHasCredential("resolvecomment")) //allow user to resolve it if they have the privilege
             {
                $resolved =  "<a title='Click to Mark as Resolved' href='javascript:;' onClick=\"ajaxresolve('/backend.php/tasks/toggledecline/id/".$decline->getId()."/resolved/1','d_".$decline->getId()."');\">";
             }

             $resolved = $resolved."<span class='glyphicon glyphicon-ban-circle'></span> Not Resolved."; 

             if($sf_user->mfHasCredential("resolvecomment"))
             {
                $resolved =  $resolved."</a>";
             }
        }
        else //If previous reason for decline was resolved
        {
             if($sf_user->mfHasCredential("resolvecomment")) //allow user to mark as unresolved if they have the privilege
             {
                $resolved =  "<a title='Click to Mark as Not Resolved.' href='javascript:;' onClick=\"ajaxunresolve('/backend.php/tasks/toggledecline/id/".$decline->getId()."/resolved/0','d_".$decline->getId()."');\">";
             }

             $resolved = $resolved."<span class='glyphicon glyphicon-ok'></span> Resolved."; 

             if($sf_user->mfHasCredential("resolvecomment"))
             {
                $resolved =  $resolved."</a>";
             }
        }

        $count++;
        //Display the reason for decline
        echo "<tr><td>".$count."</td><td>".$decline->getDescription()."</td><td><div id='d_".$decline->getId()."'>".$resolved."</div></td>";
    }
    ?>
    </tbody>
 </table>
<?php
}
?>