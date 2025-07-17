<div class="card-box widget-user" style="padding: 10px;">
    <h4 class="m-t-0 m-b-20 header-title"><b><?php echo __("Messages"); ?></b></h4>
    <div class="chat-conversation">
        <ul class="conversation-list nicescroll" id="message_block">
            <?php
            $q = Doctrine_Query::create()
                ->from('Communications a')
                ->where('a.application_id = ?', $application->getId())
                ->orderBy('a.id ASC');
            $communications = $q->execute();
			$profile_img='';
			if(strlen($application->getSfGuardUser()->getSfGuardUserProfile()->getProfilePic())){
				$profile_img="<img style=\"height: 40px;\" src=\"/assets_frontend/images/{$application->getSfGuardUser()->getSfGuardUserProfile()->getProfilePic()}\">";
			}else{
				$profile_img="<img style=\"height: 40px;\" src=\"/assets_frontend/images/avatar-1.jpg\">";
			}
			foreach($communications as $message) {
				if($message->getArchitectId() != "") {

					$fullname = $message->getSfGuardUser()->getSfGuardUserProfile()->getFullname();
					?>
					<li class="clearfix">
						<div class="chat-avatar">
							<?php echo $profile_img ?>
							<i><?php echo $message->getActionTimestamp(); ?></i>
						</div>
						<div class="conversation-text">
							<div class="ctext-wrap">
								<i><?php echo $fullname; ?></i>

								<p>
									<?php echo html_entity_decode($message->getContent()); ?>
								</p>
							</div>
						</div>
					</li>
				<?php
				}
				else if($message->getReviewerId() != "") {
					//set as read
					$message->setMessageRead("1");
					$message->save();

					$fullname = $message->getCfUser()->getStrfirstname()." ".$message->getCfUser()->getStrlastname()
					?>
					<li class="clearfix odd">
						<div class="chat-avatar">
							<img style="height: 40px;" src="/assets_frontend/images/avatar-1.jpg">
							<i><?php echo $message->getActionTimestamp(); ?></i>
						</div>
						<div class="conversation-text">
							<div class="ctext-wrap">
								<i><?php echo $fullname; ?></i>

								<p>
									<?php echo $message->getContent(); ?>
								</p>
							</div>
						</div>
					</li>
					<?php
				}
			}
            ?>

        </ul>
        <div class="row" style="padding: 10px;">
            <form action="javascript:;" method="post"  autocomplete="off" id="message_form">
                <div class="col-sm-9 chat-inputbar">
					<input type="hidden" name="id" value="<?php echo $application->getId() ?>" />
                    <textarea name="txtmessage" class="form-control chat-input" placeholder="<?php echo __("Enter your text"); ?>" id="msg_wysiwyg"></textarea>
                    
                    <br>
                    
                    <button type="submit" class="btn btn-md btn-primary waves-effect waves-light"><?php echo __("Send"); ?></button>
                </div>
            </form>
        </div>
	</div>
</div>