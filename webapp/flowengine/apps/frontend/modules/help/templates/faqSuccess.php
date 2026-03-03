<?php
/**
 * faqSuccess.php template.
 *
 * Displays list of published faq
 *
 * @package    frontend
 * @subpackage faq
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

use_helper('I18N');
?>
  <!-- Start Nav Backed Header -->
  <div class="nav-backed-header parallax">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <ol class="breadcrumb">
            <li><a href="<?php echo url_for('@dashboard') ?>">Home</a></li>
            <li class="active"><?php echo __('Frequently Asked Questions'); ?></li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <!-- End Nav Backed Header --> 
  <!-- Start Page Header -->
  <div class="page-header">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h1><?php echo __('Frequently Asked Questions'); ?></h1>
        </div>
      </div>
    </div>
  </div>
  <!-- End Page Header -->


  <div class="main" role="main">
    <div id="content" class="content full">
      <div class="container">
        <div class="row">
          <div class="col-md-12"> 

            <div class="listing post-listing">
              <section class="listing-cont">
                <ul>
			  <?php
			  $q = Doctrine_Query::create()
			  ->from("Faq a");
			  $basicfaqs = $q->execute();
			  foreach($basicfaqs as $faq)
			  {
			  ?>
                  <li class="item post">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="post-title">
                          <h2><?php echo html_entity_decode($faq->getQuestion()); ?></h2>
						</div>
                        <p><?php echo html_entity_decode($faq->getAnswer()); ?></p>
                      </div>
                    </div>
                  </li>
			  <?php
			  }
			  ?>
			  </ul>
				</section>
				
            </div>
</div>
</div>
</div>
</div>
</div>