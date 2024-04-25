<?php use_helper('I18N') ?>

<br/>
<br/>
<br/>
<div class="panel">
			<legend>Lost Password Recovery</legend>
			
              <div class="alert alert-info">
                      <?php echo(__(<<<EOM
That account was never verified. You must verify the account before you can log in or, if
necessary, reset the password. We have resent your verification email, which contains
instructions to verify your account. If you do not see that email, please be sure to check 
your "spam" or "bulk" folder.
EOM
)) ?>
			   </div>
	  
<?php include_partial('sfApply/continue') ?>
	  
	  
	  
	  
</div>









      











