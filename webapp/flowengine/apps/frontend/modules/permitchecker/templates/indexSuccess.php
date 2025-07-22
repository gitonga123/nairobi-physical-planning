<?php
/**
 * indexSuccess.php template.
 *
 * Displays list of all of the currently logged in client's permits
 *
 * @package    frontend
 * @subpackage permits
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>




<div class="contentpanel">

    <div class="panel panel-dark widget-btns">

        <div class="panel-body panel-body-nopadding">
  <section class="container page-content" >
    <hr class="vertical-space2">
    <section id="main-content">
<div class="sixteen columns">


			<form action="permits/openrequest" method="GET">
				<p>This form allows you to confirm whether a particular permit is genuine or not. Enter the permit number you wish to validate below: </p><input type="text" name="permitref"><br/>
				<input type="submit" value="Enter">
			</form>
</div>
</section><!-- end-main-conten -->
    <hr class="vertical-space2">
  </section>
  <!-- container -->

        </div><!-- panel-body-nopadding pt10 -->
    </div><!-- panel -->

</div><!--contentpanel-->
