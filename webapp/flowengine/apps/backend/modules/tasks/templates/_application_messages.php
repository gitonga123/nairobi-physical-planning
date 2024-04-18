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
	->from('Communications a')
	->where('a.application_id = ?', $application->getId())
	->orderBy('a.id ASC');
$messages = $q->execute();
?>
<div class="pull-right">
	<button class="btn btn-danger" id="mark_as_read">Mark as Read</button>
</div>
<br /><br />
<form>

	<div class="panel panel-default panel-messages mt0 scrollable" data-height="450" id="message_block">
		<?php
		//Iterate through each message and display them
		foreach ($messages as $message) {
			$class = 'media';
			if ($message->getReviewerId() != "") {
				$name = $message->getCfUser()->getStrfirstname() . " " . $message->getCfUser()->getStrlastname();
				$class .= ' media-client';
			} else if ($message->getArchitectId() != "") {
				$name = $message->getSfGuardUser()->getSfGuardUserProfile()->getFullname() . " (Client)";
				$class .= ' media-staff';
			}
		?>
			<div class="<?php echo $class ?>">
				<span class="arrow"></span>
				<div class="media-body">
					<h4 class="text-primary"><?php echo $name; ?> </h4>
					<?php echo html_entity_decode($message->getContent()); ?>
					<small class="text-muted pull-right"><?php echo $message->getActionTimestamp(); ?></small>
				</div>
				<span class="clearfix"></span>
			</div><!-- media -->

		<?php
		}
		?>
	</div>
</form>

<!-- Display form for reviewer to send message/reply to architect -->
<form action="javascript:;" method="post" autocomplete="off" id="message_form">
	<div class="panel panel-default timeline-post mb0">
		<div class="panel-body pt0">
			<input type="hidden" name="id" value="<?php echo $application->getId() ?>" />
			<textarea name='txtmessage' id="msg_wysiwyg" placeholder="<?php echo __("Type in your Message Here"); ?>..." class="form-control" rows="10" data-autogrow="true"></textarea>
		</div>
	</div>
	<div class="panel-footer">
		<button type='submit' class="btn btn-primary pull-right"><?php echo __("Send"); ?> </button>
	</div>
</form>
<script>
	$(function() {
		$('#message_form').submit(function(e) {
			$.ajax({
				url: "<?php echo url_for('/backend.php/tasks/messaging') ?>",
				data: $(this).serialize(),
				type: "POST",
				dataType: "json",
			}).done(function(resp) {
				if (resp.success) {
					$('#message_block').append('<div class="media media-staff"><span class="arrow"></span><div class="media-body"><h4 class="text-primary">' + resp.message.name + '</h4>' + resp.message.content + '<small class="text-muted pull-right">' + resp.message.time + '</small></div><span class="clearfix"></span></div>');
				} else {
					alert('Message not created!');
				}
				$('#msg_wysiwyg').val('');
			}).fail(function(xhr, status, errorThrown) {
				alert('Error!');
			});
			e.preventDefault();
		});

		$("#mark_as_read").click(function(e) {
			e.preventDefault();
			$.ajax({
				url: "<?php echo url_for("/backend.php/tasks/markAsRead/id/{$application->getId()}"); ?>",
				type: "GET"
			}).done(function(resp) {
				alert("All messages marked as read.");
			})
		});
	});
</script>