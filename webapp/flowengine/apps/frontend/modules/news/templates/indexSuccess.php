<?php
/**
 * indexSuccess.php template.
 *
 * Displays list of published news articles
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
            <li class="active"><?php echo __('News Articles'); ?></li>
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
          <h1><?php echo __('News Articles'); ?></h1>
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
    <?php
    if (!$pager->getNbResults()) {
        echo __('No News Articles Posted');
    }
    ?>
    <section class="container page-content" >
        <hr class="vertical-space2">
         <?php
            // Display list of available news articles
            foreach($pager->getResults() as $content) {
                ?>
                <article class="blog-post">

                    <!-- Display date summary on the left of the news article -->
                    <div class="one columns alpha">
                        <div class="blog-date-sec">
                            <!-- Month -->
                            <span><?php echo format_date($content->getCreatedOn(), "MMM"); ?></span>
                            <!-- Day-->
                            <h3><?php echo format_date($content->getCreatedOn(), "dd"); ?></h3>
                            <!-- Year -->
                            <span><?php echo format_date($content->getCreatedOn(), "yyyy"); ?></span>
                        </div>
                    </div>

                    <!-- Display summary of the news article -->
                    <div class="fifteen columns omega">
                        <h3>
                            <a href="/plan//news/article/id/<?php echo $content->getId(); ?>"><?php echo html_entity_decode($content->getTitle()); ?></a>
                        </h3>
                        <div class="postmetadata">
                            <!-- Name of author -->
                            <h6 class="blog-author"><strong>by</strong>
                                <?php
                                $q = Doctrine_Query::create()
                                    ->from('cfUser a')
                                    ->where('a.nid = ?', $content->getCreatedBy());
                                $user = $q->fetchone();
                                if ($user) {
                                    echo $user->getStrfirstname() . " " . $user->getStrlastname();
                                } else {
                                    echo sfConfig::get("app_organisation_name");
                                }
                                ?>
                            </h6>
                        </div>

                        <!-- Display a summary of the news article -->
                        <?php
                        $newscontent = html_entity_decode($content->getArticle());
                        if (strlen($newscontent) > 400) {
                            echo substr($newscontent, 0, 400) . ".....";
                            echo '</p><a class="readmore" href="/plan//news/article/id/' . $content->getId() . '">'.__("Read more").'</a>';
                        } else {
                            echo html_entity_decode($content->getArticle());
                        }
                        ?>
                    </div>

                    <br class="clear">

                </article>
                <?php
            }
            ?>

            <br class="clear">

            <!-- Display pagination -->
            <?php if ($pager->haveToPaginate()): ?>
                  <div class="pagination2 pagination2-centered">
                    <ul>
                      <li><?php echo link_to(image_tag(sfConfig::get('sf_admin_web_dir').'/assets_backend/images/first.png', array('align' => 'absmiddle', 'alt' => 'First', 'title' => 'First')), 'news/index?page=1') ?></li>
                      <li><?php echo link_to(image_tag(sfConfig::get('sf_admin_web_dir').'/assets_backend/images/previous.png', array('align' => 'absmiddle', 'alt' => 'Previous', 'title' => 'Previous')), 'news/index?page='.$pager->getPreviousPage()) ?></li>
                        <li><?php foreach ($pager->getLinks() as $page): ?>
                            <?php echo link_to_unless($page == $pager->getPage(), $page, 'news/index?page='.$page) ?></li>
                        <?php endforeach; ?>
                      <li><?php echo link_to(image_tag(sfConfig::get('sf_admin_web_dir').'/assets_backend/images/next.png', array('align' => 'absmiddle', 'alt' => 'Next', 'title' => 'Next')), 'news/index?page='.$pager->getNextPage()) ?></li>
                      <li><?php echo link_to(image_tag(sfConfig::get('sf_admin_web_dir').'/assets_backend/images/last.png', array('align' => 'absmiddle', 'alt' => 'Last', 'title' => 'Last')), 'news/index?page='.$pager->getLastPage()) ?></li>

                    </ul>
                  </div>
            <?php endif; ?>

            <div class="vertical-space2"></div>

            <!-- end-main-content -->
            <hr class="vertical-space2">
	</section>
</div>
</div>
</div>
</div>
</div>
