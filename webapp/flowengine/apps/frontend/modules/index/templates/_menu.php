<?php
/**
 * _menu.php template.
 *
 * Displays Menu
 *
 * @package    frontend
 * @subpackage index
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
  use_helper('I18N');
  ?>

  <div class="col-xl-8 col-lg-10 col-md-12">
  <nav class="mainmenu text-center">
      <ul>
<?php
foreach ($pages as $page) { 
    $cls = ($_SERVER['REQUEST_URI'] == url_for($page->getUrl()) || ($_SERVER['REQUEST_URI'] == "/" && $x == 1)) ? 'current': '';
?>
<?php
$q = Doctrine_Query::create()
->from('Content a')
->where('a.published = ? and a.parent_id = ?', array(1,$page->getId()))
->andWhere('a.visibility = 1')
->orderBy('a.menu_index ASC');
$child_pages_count=$q->count();
?>
    <li class="<?php echo $cls ?> <?php $child_pages_count? 'menu-item-has-children': '' ?>">
        <a href="/index.php/?id=<?php echo $page->getId(); ?>" title="<?php echo $page->getMenuTitle(); ?>"><?php echo $page->getMenuTitle() ?></a>
        <?php if($child_pages_count): ?>
        <ul class="sub_menu">
            <?php foreach($q->execute() as $child_page): ?>
            <li class=" menu-item-has-children">
                <a href="/index.php/?id=<?php echo $child_page->getId(); ?>" title="<?php echo $child_page->getMenuTitle(); ?>"><?php echo $child_page->getMenuTitle() ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </li>
<?php 
} 
$cls = ($_SERVER['REQUEST_URI'] == "/index.php//news/index" || $_SERVER['REQUEST_URI'] == "/index.php//new/index") ? 'current': '';
?>
    <li class="<?php echo $cls ?>"><a href="/index.php//news/index" title="<?php echo __('News'); ?>"><?php echo __('News'); ?> </a></li>
<?php
$cls = ($_SERVER['REQUEST_URI'] == "/index.php//help/faq" || $_SERVER['REQUEST_URI'] == "/index.php//help/faq") ? 'current': '';
?>
    <li class="<?php echo $cls ?>"><a href="/index.php//help/faq" title="<?php echo __('FAQs'); ?>"><?php echo __('FAQs'); ?> </a></li>
<?php
$cls = ($_SERVER['REQUEST_URI'] == "/index.php//help/contact" || $_SERVER['REQUEST_URI'] == "/index.php//help/contact") ? 'current': '';
?>
    <li class="<?php echo $cls ?>"><a href="/index.php//help/contact" title="<?php echo __('Contact Us'); ?>"><?php echo __('Contact Us'); ?> </a></li>

    <li>
        <a href="<?php echo url_for('signon/login') ?>" class="text-center"><span><?php echo __('Log in'); ?></span></a>
    </li>

    <li>
        <a href="<?php echo url_for('signon/register') ?>" class="text-center"><span><?php echo __('Register'); ?></span></a>
    </li>
    
      </ul>
  </nav>
</div>
