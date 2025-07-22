<?php
/**
 * sendnotificationSuccess.php template.
 *
 * Allows sending of notifications to selected clients
 *
 * @package    backend
 * @subpackage frusers
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
?>
<div class="g12" style="margin-top: -5px;">
        <form action='/plan/frusers/notificationmail' method='post' >
         <fieldset>
         <label>Send Notification</label>
         <section>
         <label>Email</label>
         <div>
          <select name='emails' id='emails' multiple>
          <?php
                $q = Doctrine_Query::create()
                          ->from('sfGuardUserProfile a')
                          ->orderBy('a.Fullname ASC');
                $profiles = $q->execute();
                foreach($profiles as $profile)
                {
                        echo "<option value='".$profile->getEmail()."'>".$profile->getFullname()."</option>";
                }
          ?>
          </select>
         </div>
         </section>
         <section>
         <label>Subject</label>
         <div>
                <input type='text' name='subject' id='subject' value=''>
         </div>
         </section>
         <section>
         <label>Mail</label>
         <div>
          <textarea name='mail' id='mail'></textarea>
         </div>
         </section>
         <section>
                        <div><button class="reset">Reset</button><button class="submit" name="submitbuttonname" value="submitbuttonvalue">Submit</button></div>
         </section>
         </fieldset>
        </form>
					
</div>