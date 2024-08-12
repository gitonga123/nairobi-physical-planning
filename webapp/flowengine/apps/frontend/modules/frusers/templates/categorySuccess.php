<?php
/**
 * indexSuccess.php template.
 *
 * Displays login form for clients
 *
 * @package    sfDoctrineGuardPlugin
 * @subpackage sfGuardAuth
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');
?>

<div class="breadcrumb-box">
    <div class="container">
        <ul class="breadcrumb">
            <li>
                <a href="index.html"><?php echo __("Home"); ?></a>
                <span class="divider">/</span>
            </li>
            <li class="active"><?php echo __("Sign In"); ?></li>
        </ul>
    </div>
</div>

<section id="headline">
    <div class="container">
        <h3><?php echo __("Sign In"); ?></h3>
    </div>
</section>

<!-- BEGIN CONTAINER -->
<div class="container margin-bottom-40">
    <div class="row signinpanel">

        <div class="eleven columns offset-by-five login-signup-page mb40">

            <form method="post" id="contact-form" action="<?php echo url_for('@sf_guard_signin') ?>">
                <h4 class="nomargin"><?php echo __("Please Sign In"); ?></h4>
                <p class="mt5 mb20"><?php echo __("Login to access your account"); ?>.</p>

                <select class="form-control uname" name="category" id="category" onChange="if(confirm('Are you sure?')){ window.location='/index.php//mfRegister/registerDetails?formid=' + this.value; }"/>
                <option>Choose a category...</option>
                <?php
                $q = Doctrine_Query::create()
                    ->from('sfGuardUserCategories a')
                    ->orderBy('a.orderid ASC');
                $categories = $q->execute();
                foreach($categories as $category)
                {
                    echo "<option value='".$category->getFormid()."'>".$category->getDescription()."</option>";
                }
                ?>
                </select>
                <!-- col-sm-5 -->
            </form>
        </div>
    </div><!-- row -->
</div>
<!-- END CONTAINER -->
