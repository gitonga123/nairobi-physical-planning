<?php

/**
 * contactSuccess.php template.
 *
 * Displays a contact us form
 *
 * @package    frontend
 * @subpackage forms
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper('I18N');
?>
<!-- Breadcrumb -->
<div class="breadcrumb-bar">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-12 col-12">
                <nav aria-label="breadcrumb" class="page-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo url_for('@dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo __("Contact Us"); ?></li>
                    </ol>
                </nav>
                <h2 class="breadcrumb-title"><?php echo __("Contact Us"); ?></h2>
            </div>
        </div>
    </div>
</div>
<!-- /Breadcrumb -->

<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-xl-12 col-md-6 col-lg-7 pdl45">
                <div class="row">
                    <div class="col-xl-12 text-left">
                        <h2 class="sec_title mb45">
                            <span><?php echo __("Please leave us a message and we'll get back to you."); ?></span>
                        </h2>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card flex-fill">
                            <div class="card-header">
                                <h4 class="card-title">Contact Us</h4>
                            </div>
                            <div class="card-body">

                                <form role="form" method="post" class="contact-form">
                                    <?php
                                    if ($sent) {
                                    ?>
                                        <h3 style="margin-bottom: 25px; text-align: center;"><?php echo __("Thank you. Your message has been sent"); ?>.</h3>
                                    <?php
                                    } else {
                                    ?> <?php echo $form->renderHiddenFields() ?>
                                        <?php echo $form->renderGlobalErrors() ?>
                                        <div class="form-group row">
                                            <label class="col-lg-3 col-form-label">Name</label>
                                            <div class="col-lg-9">
                                                <?php echo $form['name']->render(["class" => "form-control input-lg", "placeholder" => __("Name")]) ?>
                                                <span><?php echo $form['name']->renderError() ?></span>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-lg-3 col-form-label">Email Address</label>
                                            <div class="col-lg-9">
                                                <?php echo $form['email']->render(["class" => "form-control input-lg", "placeholder" => __("Email")]) ?>
                                                <span><?php echo $form['email']->renderError() ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-lg-3 col-form-label">Mobile Number</label>
                                            <div class="col-lg-9">
                                                <?php echo $form['mobile']->render(["class" => "form-control input-lg", "placeholder" => __("Mobile Number")]) ?>
                                                <span><?php echo $form['mobile']->renderError() ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-lg-3 col-form-label">Subject</label>
                                            <div class="col-lg-9">
                                                <?php echo $form['subject']->render(["class" => "form-control input-lg", "placeholder" => __("Subject")]) ?>
                                                <span><?php echo $form['subject']->renderError() ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-lg-3 col-form-label">Message</label>
                                            <div class="col-lg-9">
                                                <?php echo $form['message']->render(["class" => "form-control", "placeholder" => __("Message")]) ?>
                                                <span><?php echo $form['message']->renderError() ?></span>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary"><?php echo __("Submit Form"); ?></button>
                                        </div>
                                    <?php } ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>