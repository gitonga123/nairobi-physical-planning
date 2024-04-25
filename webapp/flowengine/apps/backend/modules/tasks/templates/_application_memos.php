<?php
/**
 * _viewmessages.php partial.
 *
 * Displays a message trail between the client and the reviewers
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
 use_helper("I18N");


//Get all messages attached to this application
$q = Doctrine_Query::create()
  ->from('Communication a')
  ->where('a.application_id = ?', $application->getId())
  ->orderBy('a.id ASC');
$memos = $q->execute();

//if($sf_user->mfHasCredential('incomingmessages'))
//{
?>

    <form>

	<div class="panel panel-default panel-messages mt0 scrollable" data-height="450" id="memo_block">
     <?php
		//Iterate through each memos and display them
	 	foreach($memos as $memo){
			$name = $memo->getCfUser()->getStrfirstname()." ".$memo->getCfUser()->getStrlastname();
			?>
			<div class="media media-client">
				<span class="arrow"></span>
				<div class="media-body">
					<h4 class="text-primary"><?php echo $name; ?> </h4>
					<?php echo html_entity_decode($memo->getMessage()); ?>
					<small class="text-muted pull-right"><?php echo $memo->getCreatedOn(); ?></small>
				</div>
				<span class="clearfix"></span>
			</div><!-- media -->
      <?php
		}
    ?>
	</div>
	
	</form>

        <!-- Display form for reviewer to send memo/reply to other reviewers -->
        <form action="javascript:;" method="post"  autocomplete="off" id="memo_form">
              <div class="panel panel-default timeline-post mb0">
                 <div class="panel-body pt0">
					<input type="hidden" name="id" value="<?php echo $application->getId() ?>" />
					<textarea name='txtmemo' id="memo_wysiwyg" placeholder="<?php echo __("Type in your Memo Here"); ?>..." class="form-control" rows="10" data-autogrow="true"></textarea>
                 </div>
              </div>
               <div class="panel-footer">
                      <button type='submit' class="btn btn-primary pull-right"><?php echo __("Send"); ?> </button>
             </div>
         </form>
<script>
$(function(){
	$('#memo_form').submit(function(e){
		$.ajax({
			url: "<?php echo url_for('/backend.php/tasks/messaging') ?>",
			data: $(this).serialize(),
			type: "POST",
			dataType: "json",
		}).done(function(resp){
			if(resp.success){
				$('#memo_block').append('<div class="media media-client"><span class="arrow"></span><div class="media-body"><h4 class="text-primary">'+resp.message.name+'</h4>'+resp.message.content+'<small class="text-muted pull-right">'+resp.message.time+'</small></div><span class="clearfix"></span></div>');
			}else{
				alert('Memo not created!');
			}
			$('#memo_wysiwyg').val('');
		}).fail(function(xhr,status,errorThrown){
			alert('Error!');
			console.log("Error: "+errorThrown);
			console.log("Status: "+status);
			console.dir(xhr);
		});
		e.preventDefault();
	});
});
</script>