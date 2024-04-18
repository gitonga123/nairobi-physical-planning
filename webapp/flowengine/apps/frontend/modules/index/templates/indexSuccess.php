<?php
/**
 * indexSuccess.php template.
 *
 * Displays a web page
 *
 * @package    frontend
 * @subpackage index
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

use_helper('I18N');

//Display breadcrumbs if id parameter has been set
if($page->getId() != 1)
{
?>
        <section class="page_banner">
            <div class="container">
                <div class="row">
                    <div class="col-xl-12 text-center">
                        <h2><?php echo $page->getMenuTitle(); ?></h2>
                        <div class="breadcrumbs">
                            <a href="/">Home</a><i>|</i><span><?php echo $page->getMenuTitle(); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

<?php
}

//Display banner if homepage
if($page->getId() == 1)
{
    include_component('index', 'banner');
}


$article = html_entity_decode($page->getTopArticle());
echo $article;

if($page->getId() == 1){
    include_partial('index/latestnews');
}
