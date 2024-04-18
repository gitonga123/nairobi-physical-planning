<?php
/**
 * articleSuccess.php template.
 *
 * Displays full news article
 *
 * @package    frontend
 * @subpackage news
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

use_helper('I18N', 'Date');
?>
  <!-- Start Nav Backed Header -->
  <div class="nav-backed-header parallax">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <ol class="breadcrumb">
            <li><a href="<?php echo url_for('/') ?>">Home</a></li>
            <li class="active"><?php echo __('News'); ?></li>
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
          <h1><?php echo __('News'); ?></h1>
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

<!-- Display news article -->
<section class="container page-content" >
    <section class="sixteen columns">
        <article class="blog-single-post">
            <div class="post">
                <div class="postmetadata">
                    <h6 class="blog-author"><?php echo format_date($news->getCreatedOn(), "D"); ?> /  <?php
                        $q = Doctrine_Query::create()
                        ->from('cfUser a')
                        ->where('a.nid = ?', $news->getCreatedBy());
                        $user = $q->fetchOne();
                        if($user)
                        {
                            echo " <strong>by</strong> ".$user->getStrfirstname()." ".$user->getStrlastname();
                        }
                        else
                        {
                            echo " <strong>by</strong> ".sfConfig::get("app_organisation_name");
                        }
                    ?> </h6>
                </div>

                <h1><?php echo $news->getTitle(); ?></h1>

                <?php echo html_entity_decode($news->getArticle());?>

                <br class="clear">

            </div>
        </article>

    </section>

    <div class="white-space"></div>

</section>

</div>
</div>
</div>
</div>
</div>