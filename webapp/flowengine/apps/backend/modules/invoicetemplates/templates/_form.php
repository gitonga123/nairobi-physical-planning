<?php
use_helper("I18N");
?>
<form action="/plan/invoicetemplates/<?php echo ($form->getObject()->isNew() ? 'create' : 'update') . (!$form->getObject()->isNew() ? '?id=' . $form->getObject()->getId() : ''); ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?> autocomplete="off" data-ajax="false" class="form-bordered">

  <?php if (!$form->getObject()->isNew()) : ?>
    <input type="hidden" name="sf_method" value="put" />
  <?php endif; ?>

  <?php if (isset($form['_csrf_token'])) : ?>
    <?php echo $form['_csrf_token']->render(); ?>
  <?php endif; ?>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo ($form->getObject()->isNew() ? __('New Invoice Template') : __('Edit Invoice Template')); ?></h3>
      <?php echo $form->renderGlobalErrors() ?>
    </div>

    <div class="panel-heading text-right">
      <a class="btn btn-primary" id="newpage" href="/plan/invoicetemplates/index"><?php echo __('Back to List'); ?></a>
    </div>

    <div class="panel-body padding-0">
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Title'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['title']->renderError() ?>
          <?php echo $form['title'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Application Form'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['applicationform']->renderError() ?>
          <?php echo $form['applicationform'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Application Stage'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['applicationstage']->renderError() ?>
          <?php echo $form['applicationstage'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Invoice number of the first invoice'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['invoice_number']->renderError() ?>
          <?php echo $form['invoice_number'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Type of payment'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['payment_type']->renderError() ?>
          <?php echo $form['payment_type'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Type of expiration'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['expiration_type']->renderError() ?>
          <?php echo $form['expiration_type'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Max number of days before expiration'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['max_duration']->renderError() ?>
          <?php echo $form['max_duration'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Content'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['content']->renderError() ?>
          <?php echo $form['content'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('QR Content'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['qr_content']->renderError() ?>
          <?php echo $form['qr_content'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Receipt'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['receipt_content']->renderError() ?>
          <?php echo $form['receipt_content'] ?>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label"><?php echo __('Receipt QR Code'); ?></label><br>
        <div class="col-sm-12">
          <?php echo $form['receipt_content_qr_code']->renderError() ?>
          <?php echo $form['receipt_content_qr_code'] ?>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-12">
          <button type="button" class="btn btn-primary pull-right" data-target="#fieldsModal" data-toggle="modal">View available user/form fields</button>
        </div>
      </div>
    </div><!-- panel-body -->
    <div class="panel-footer">
      <button type="submit" class="btn btn-primary"><?php echo __('Submit'); ?></button>
    </div>
  </div><!-- panel-default -->
</form>

<!-- MODAL BOX -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="fieldsModal" class="modal fade" style="display: none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
        <h4 id="myModalLabel" class="modal-title">View available user/form fields</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <?php
          if (!$form->getObject()->isNew()) {
            $appform = $form->getObject()->getApplicationform();
          ?>

            <table class="table dt-on-steroids mb0">
              <thead>
                <tr>
                  <th width="50%"><?php echo __('Applicant Details'); ?></th>
                  <th><?php echo __('Tag'); ?></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><?php echo __('User ID'); ?></td>
                  <td>{sf_username}</td>
                </tr>
                <tr>
                  <td><?php echo __('Mobile Number'); ?></td>
                  <td>{sf_mobile}</td>
                </tr>
                <tr>
                  <td><?php echo __('Email'); ?></td>
                  <td>{sf_email}</td>
                </tr>
                <tr>
                  <td><?php echo __('Full Name'); ?></td>
                  <td>{sf_fullname}</td>
                </tr>
                <?php
                $q = Doctrine_Query::create()
                  ->from('apFormElements a')
                  ->where('a.form_id = ?', 15)
                  ->andWhere('a.element_status = ?', 1);

                $elements = $q->execute();

                foreach ($elements as $element) {
                  $childs = $element->getElementTotalChild();
                  if ($childs == 0) {
                    echo "<tr><td>" . $element->getElementTitle() . "</td><td>{sf_element_" . $element->getElementId() . "}</td></tr>";
                  } else {
                    if ($element->getElementType() == "select") {
                      echo "<tr><td>" . $element->getElementTitle() . "</td><td>{sf_element_" . $element->getElementId() . "}</td></tr>";
                    } else {
                      for ($x = 0; $x < ($childs + 1); $x++) {
                        echo "<tr><td>" . $element->getElementTitle() . "</td><td>{sf_element_" . $element->getElementId() . "_" . ($x + 1) . "}</td></tr>";
                      }
                    }
                  }
                }
                ?>
              </tbody>
            </table>

            <table class="table dt-on-steroids mb0">
              <thead>
                <tr>
                  <th width="50%"><?php echo __('Application Details'); ?></th>
                  <th><?php echo __('Tag'); ?></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><?php echo __('Application Number'); ?></td>
                  <td>{ap_application_id}</td>
                </tr>
                <tr>
                  <td><?php echo __('Created At'); ?></td>
                  <td>{fm_created_at}</td>
                </tr>
                <tr>
                  <td><?php echo __('Approved At'); ?></td>
                  <td>{fm_updated_at}</td>
                </tr>
                <?php
                if (!$form->getObject()->isNew()) {
                  $appform = $form->getObject()->getApplicationform();
                ?>
                  <?php

                  $q = Doctrine_Query::create()
                    ->from('apFormElements a')
                    ->where('a.form_id = ?', $appform)
                    ->andWhere('a.element_status = ?', 1)
                    ->orderBy('a.element_position ASC');

                  $elements = $q->execute();

                  foreach ($elements as $element) {
                    $childs = $element->getElementTotalChild();
                    if ($childs == 0) {
                      if ($element->getElementType() == "select") {
                        if ($element->getElementExistingForm() && $element->getElementExistingStage()) {
                          $q = Doctrine_Query::create()
                            ->from("ApForms a")
                            ->where("a.form_id = ?", $element->getElementExistingForm());
                          $child_form = $q->fetchOne();

                          echo "<tr><td>" . $element->getElementTitle() . "</td><td>{fm_element_" . $element->getElementId() . "} ";
                          echo '<table class="table dt-on-steroids mb0">
                            <thead><tr><th width="50%">' . __($child_form->getFormName() . ' Details') . '</th><th>' . __('Tag') . '</th></tr></thead>
                            <tbody>';

                  ?>
                          <tr>
                            <td><?php echo __('Application Number'); ?></td>
                            <td>{ap_child_application_id}</td>
                          </tr>
                          <tr>
                            <td><?php echo __('Created At'); ?></td>
                            <td>{fm_child_created_at}</td>
                          </tr>
                          <tr>
                            <td><?php echo __('Approved At'); ?></td>
                            <td>{fm_child_updated_at}</td>
                          </tr>
                          <?php
                          $q = Doctrine_Query::create()
                            ->from("Permits a")
                            ->where("a.applicationform = ?", $element->getElementExistingForm());
                          $permits = $q->execute();

                          foreach ($permits as $permit) {
                            echo "<tr><td>" . $permit->getTitle() . " ID</td><td>{ap_permit_id_" . $permit->getId() . "_element_child}</td></tr>";
                          }

                          $q = Doctrine_Query::create()
                            ->from('apFormElements a')
                            ->where('a.form_id = ?', $element->getElementExistingForm())
                            ->andWhere('a.element_status = ?', 1)
                            ->orderBy('a.element_position ASC');

                          $child_elements = $q->execute();

                          foreach ($child_elements as $child_element) {

                            //START CHILD ELEMENTS
                            $childs = $child_element->getElementTotalChild();
                            if ($childs == 0) {
                              if ($child_element->getElementType() == "select") {
                                if ($child_element->getElementExistingForm() && $child_element->getElementExistingStage()) {

                                  $q = Doctrine_Query::create()
                                    ->from("ApForms a")
                                    ->where("a.form_id = ?", $child_element->getElementExistingForm());
                                  $grand_form = $q->fetchOne();

                                  echo "<tr><td>" . $child_element->getElementTitle() . "</td><td>{fm_child_element_" . $child_element->getElementId() . "} ";
                                  echo '<table class="table dt-on-steroids mb0">
                                          <thead><tr><th width="50%">' . __($grand_form->getFormName() . ' Details') . '</th><th>' . __('Tag') . '</th></tr></thead>
                                          <tbody>';

                                  $q = Doctrine_Query::create()
                                    ->from('apFormElements a')
                                    ->where('a.form_id = ?', $child_element->getElementExistingForm())
                                    ->andWhere('a.element_status = ?', 1)
                                    ->orderBy('a.element_position ASC');

                                  $grand_child_elements = $q->execute();

                                  foreach ($grand_child_elements as $grand_child_element) {
                          ?>
                                    <tr>
                                      <td><?php echo __('Application Number'); ?></td>
                                      <td>{ap_grand_child_application_id}</td>
                                    </tr>
                                    <tr>
                                      <td><?php echo __('Created At'); ?></td>
                                      <td>{fm_grand_child_created_at}</td>
                                    </tr>
                                    <tr>
                                      <td><?php echo __('Approved At'); ?></td>
                                      <td>{fm_grand_child_updated_at}</td>
                                    </tr>
                  <?php
                                    $q = Doctrine_Query::create()
                                      ->from("Permits a")
                                      ->where("a.applicationform = ?", $child_element->getElementExistingForm());
                                    $permits = $q->execute();

                                    foreach ($permits as $permit) {
                                      echo "<tr><td>" . $permit->getTitle() . " ID</td><td>{ap_permit_id_" . $permit->getId() . "_element_grand_child}</td></tr>";
                                    }

                                    //START GRAND CHILD ELEMENTS
                                    $childs = $grand_child_element->getElementTotalChild();
                                    if ($childs == 0) {
                                      if ($grand_child_element->getElementType() == "select") {
                                        echo "<tr><td>" . $grand_child_element->getElementTitle() . "</td><td>{fm_grand_child_element_" . $grand_child_element->getElementId() . "}</td></tr>";
                                      } else {
                                        echo "<tr><td>" . $grand_child_element->getElementTitle() . "</td><td>{fm_grand_child_element_" . $grand_child_element->getElementId() . "}</td></tr>";
                                      }
                                    } else {
                                      for ($x = 0; $x < ($childs + 1); $x++) {
                                        echo "<tr><td>" . $grand_child_element->getElementTitle() . "</td><td>{fm_grand_child_element_" . $grand_child_element->getElementId() . "_" . ($x + 1) . "}</td></tr>";
                                      }
                                    }
                                    //END GRAND CHILD ELEMENTS
                                  }

                                  echo '</tbody></table>';
                                  echo "</td></tr>";
                                } else {
                                  echo "<tr><td>" . $child_element->getElementTitle() . "</td><td>{fm_child_element_" . $child_element->getElementId() . "}</td></tr>";
                                }
                              } else {
                                echo "<tr><td>" . $child_element->getElementTitle() . "</td><td>{fm_child_element_" . $child_element->getElementId() . "}</td></tr>";
                              }
                            } else {
                              for ($x = 0; $x < ($childs + 1); $x++) {
                                echo "<tr><td>" . $child_element->getElementTitle() . "</td><td>{fm_child_element_" . $child_element->getElementId() . "_" . ($x + 1) . "}</td></tr>";
                              }
                            }
                            //END CHILD ELEMENTS
                          }

                          echo '</tbody></table>';
                          echo "</td></tr>";
                        } else {
                          echo "<tr><td>" . $element->getElementTitle() . "</td><td>{fm_element_" . $element->getElementId() . "}</td></tr>";
                        }
                      } else {
                        echo "<tr><td>" . $element->getElementTitle() . "</td><td>{fm_element_" . $element->getElementId() . "}</td></tr>";
                      }
                    } else {
                      for ($x = 0; $x < ($childs + 1); $x++) {
                        echo "<tr><td>" . $element->getElementTitle() . "</td><td>{fm_element_" . $element->getElementId() . "_" . ($x + 1) . "}</td></tr>";
                      }
                    }
                  }
                  ?>
                <?php
                }
                ?>
              </tbody>
            </table>

            <table class="table dt-on-steroids mb0">
              <thead>
                <tr>
                  <th width="50%"><?php echo __('Conditions Of Approval'); ?></th>
                  <th><?php echo __('Tag'); ?></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><?php echo __('Conditions Of Approval'); ?></td>
                  <td>{ca_conditions}</td>
                </tr>
              </tbody>
            </table>

          <?php
          }
          ?>
          <table class="table dt-on-steroids mb0">
            <thead>
              <tr>
                <th width="50%"><?php echo __('Invoice Details'); ?></th>
                <th><?php echo __('Tag'); ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?php echo __('Invoice No'); ?></td>
                <td>{inv_no}</td>
              </tr>
              <tr>
                <td><?php echo __('Invoice Date'); ?></td>
                <td>{inv_date_created}</td>
              </tr>
              <tr>
                <td><?php echo __('Invoice Expiry Date'); ?></td>
                <td>{inv_expires_at}</td>
              </tr>
              <tr>
                <td><?php echo __('List of Fees'); ?></td>
                <td>{inv_fee_table}</td>
              </tr>
              <tr>
                <td><?php echo __('Total'); ?></td>
                <td>{inv_total}</td>
              </tr>
              <tr>
                <td><?php echo __('Invoice First Fee Description'); ?></td>
                <td>{inv_first_description}</td>
              </tr>
              <tr>
                <td><?php echo __('Invoice First Fee Amount'); ?></td>
                <td>{inv_first_amount}</td>
              </tr>
              <tr>
                <td><?php echo __('Invoice Last Fee Description'); ?></td>
                <td>{inv_last_description}</td>
              </tr>
              <tr>
                <td><?php echo __('Invoice Last Fee Amount'); ?></td>
                <td>{inv_last_amount}</td>
              </tr>
              <tr>
                <td><?php echo __('Balance Due'); ?></td>
                <td>{inv_balance}</td>
              </tr>
              <tr>
                <td><?php echo __('Invoice Status'); ?></td>
                <td>{inv_status}</td>
              </tr>
              <tr>
                <td><?php echo __('Payment Mode'); ?></td>
                <td>{inv_payment_merchant_type}</td>
              </tr>
              <tr>
                <td><?php echo __('Payment Reference Number'); ?></td>
                <td>{inv_payment_id}</td>
              </tr>
              <tr>
                <td><?php echo __('Bar Code'); ?></td>
                <td>{bar_code}</td>
              </tr>
              <tr>
                <td><?php echo __('Bar Code (Small)'); ?></td>
                <td>{bar_code_small}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="modal-footer">
          <button data-dismiss="modal" class="btn btn-default" type="button">Close</button>
        </div>
      </div><!-- modal-content -->
    </div><!-- modal-dialog -->

    <script src="/assets_backend/js/ckeditor/ckeditor.js"></script>
    <script src="/assets_backend/js/ckeditor/adapters/jquery.js"></script>

    <script>
      jQuery(document).ready(function() {

        // CKEditor
        jQuery('#invoicetemplates_content').ckeditor();
        jQuery('#invoicetemplates_qr_content').ckeditor();

      });
    </script>