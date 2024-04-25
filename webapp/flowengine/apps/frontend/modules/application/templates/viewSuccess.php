<?php
use_helper("I18N");
?>
<div class="col-md-8 col-lg-9 col-xl-10">

    <div class="row">

        <div class="col-12">
            <h3 class="card-title"><?php echo $application->getApplicationId(); ?></h3><small><?php echo $application->getForm()->getFormName(); ?></small>
            <p><?php echo $application->getStage()->getMenus()->getTitle() ?> - <?php echo $application->getStage()->getTitle() ?></p>
            <p style="float:right">

                <!-- Tab contents --> <!-- Tab Menu -->
            <nav class="user-tabs">
                <ul class="nav nav-tabs nav-tabs-bottom nav-justified">
                    <li>
                        <a class="nav-link active" href="#details" data-bs-toggle="tab"><?php echo __("Details"); ?></a>
                    </li>
                    <li>
                        <a class="nav-link" href="#downloads" data-bs-toggle="tab"><?php echo __("Downloads"); ?></a>
                    </li>
                    <li>
                        <a class="nav-link" href="#revisions" data-bs-toggle="tab"><?php echo __("Revisions"); ?></a>
                    </li>
                    <li>
                        <a class="nav-link" href="#revisions" data-bs-toggle="tab"><?php echo __("Other Details"); ?></a>
                    </li>
                    <li>
                        <a class="nav-link" href="#payments" data-bs-toggle="tab"><?php echo __("Payments"); ?></a>
                    </li>

                    <li>
                        <a class="nav-link" href="#reviews" data-bs-toggle="tab"><?php echo __("Reviews"); ?></a>
                    </li>
                </ul>
            </nav>
            <!-- /Tab Menu -->

            <!-- Tab Content -->
            <div class="tab-content">

                <!-- Active Content -->
                <div role="tabpanel" id="details" class="tab-pane fade show active">
                    <div class="col-12 col-md-12 col-xl-12 d-flex">
                        <?php
                        //Display form details
                        include_partial('viewdetails', array('application' => $application));
                        ?>
                    </div>

                </div>
                <!-- /Active Content -->

                <!-- Inactive Content -->
                <div role="tabpanel" id="downloads" class="tab-pane fade">
                    <div class="col-12 col-md-12 col-xl-12">

                        <?php
                        //Display downloads
                        include_partial('viewdownloads', array('application' => $application));
                        ?>
                    </div>
                </div>
                <!-- /Inactive Content -->


                <!-- Inactive Content -->
                <div role="tabpanel" id="revisions" class="tab-pane fade">

                    <div class="col-12 col-md-12 col-xl-12 d-flex">
                        <div class="card flex-fill">
                            <?php
                            //Display form details
                            include_partial('viewrevisions', array('revisions' => $revisions));
                            ?>
                        </div>
                    </div>
                </div>
                <!-- /Inactive Content -->


                <!-- Inactive Content -->
                <div role="tabpanel" id="revisions" class="tab-pane fade">

                    <div class="row">
                        <div class="col-12 col-md-12 col-xl-12 d-flex">
                            <div class="course-box blog grid-blog">
                                <?php
                                $q = Doctrine_Query::create()
                                    ->from("FormEntryLinks a")
                                    ->where("a.formentryid = ? AND a.entry_id <> ?", array($application->getId(), 0));
                                $links = $q->execute();
                                $count = 0;
                                foreach ($links as $link) {
                                    $count++;
                                    $q = Doctrine_Query::create()
                                        ->from("ApForms a")
                                        ->where("a.form_id = ?", $link->getFormId())
                                        ->limit(1);
                                    $linkedform = $q->fetchOne();
                                    if ($linkedform) {
                                        //Display control buttons that manipulate the application
                                ?>
                                        <div class="panel panel-default">
                                            <div class="panel-heading panel-heading-noradius">
                                                <h4 class="panel-title">
                                                    <a data-toggle="collapse" class="collapsed" data-parent="#accordion" href="#link_<?php echo $count ?>">
                                                        <?php echo $linkedform->getFormName() ?>
                                                    </a>
                                                </h4>
                                            </div>
                                            <div id="link_<?php echo $count ?>" class="panel-collapse collapse">
                                                <div class="panel-body">
                                                    <?php
                                                    //Displays any information attached to this application
                                                    include_partial('viewformlinks', array('link' => $link));
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Inactive Content -->


                <!-- Inactive Content -->
                <div role="tabpanel" id="payments" class="tab-pane fade">

                    <div class="col-12 col-md-12 col-xl-12 d-flex">
                        <div class="card flex-fill">
                            <?php
                            include_partial('viewpayments', array('application' => $application));
                            ?>
                        </div>
                    </div>
                </div>
                <!-- /Inactive Content -->


                <!-- Inactive Content -->
                <div role="tabpanel" id="reviews" class="tab-pane fade">

                    <div class="row">
                        <div>
                            <div class="card border-default">
                                <div class="card-body">
                                    <ul class="nav nav-tabs nav-tabs-solid nav-justified">
                                        <li class="nav-item"><a class="nav-link active" href="#solid-justified-tab1" data-bs-toggle="tab"><?php echo __('History'); ?></a></li>
                                        <li class="nav-item"><a class="nav-link" href="#solid-justified-tab2" data-bs-toggle="tab"><?php echo __("Reasons for Decline"); ?></a></li>
                                        <li class="nav-item"><a class="nav-link" href="#solid-justified-tab3" data-bs-toggle="tab"> <?php echo __("Approval Conditions"); ?></a></li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane show active" id="solid-justified-tab1">
                                            <?php
                                            //Displays any information attached to this application
                                            include_partial('application_comments', array('application' => $application));
                                            ?>
                                        </div>
                                        <div class="tab-pane" id="solid-justified-tab2">
                                            <?php
                                            include_partial('application_declines', array('application' => $application, 'form_id' => $application->getFormId(), 'entry_id' => $application->getEntryId()));
                                            ?>
                                        </div>
                                        <div class="tab-pane" id="solid-justified-tab3">
                                            <?php
                                            //Check if this application has been previously declined before
                                            include_partial('comments_conditions', array('application' => $application, 'form_id' => $application->getFormId(), 'entry_id' =>  $application->getEntryId()));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Inactive Content -->

            </div>
            <!-- /Tab Content -->

            <!-- end tab items -->

        </div>
    </div>
</div>