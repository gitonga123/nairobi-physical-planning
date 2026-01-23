<?php
use_helper('I18N');
?>
<div class="main">
    <div class="content full">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-sm-12">
                    <div class="listing post-listing">
                        <header class="listing-header">
                            <h3><?php echo __('Permit Checker') ?><span class="badge rounded-pill bg-info text-dark">Valid</span></h3>
                        </header>
                        <section class="listing-cont">
                            <?php
                            echo html_entity_decode($template);
                            ?>
                            <div class="white-space"></div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>