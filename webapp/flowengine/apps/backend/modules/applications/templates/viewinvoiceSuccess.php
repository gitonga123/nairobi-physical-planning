<?php
/**
 * view template.
 *
 * Display a task, its comments sheets/invoices and application details relating to it
 *
 * @package    backend
 * @subpackage tasks
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
  use_helper("I18N");

  $templateparser = new TemplateParser();
  $invoice_manager = new InvoiceManager();

  $application = $invoice->getFormEntry();
?>
<div class="pageheader">
  <h2><i class="fa fa-th-list"></i> <?php echo __("Tasks"); ?> <span><?php echo $application->getApplicationId(); ?></span></h2>
  <div class="breadcrumb-wrapper">
    <span class="label"><?php echo __("You are here"); ?>:</span>
    <ol class="breadcrumb">
      <li><a href="/plan/tasks/list"><?php echo __("Applications"); ?></a></li>
      <li class="active"><?php echo __("View Details"); ?></li>
    </ol>
  </div>
</div>

<div class="contentpanel">

  <div class="panel panel-default">

    <div class="panel-heading">
      <h3 class="panel-title"><?php echo __("Payment Details"); ?></h3>
    </div>


    <div class="panel-body">


    <?php
    //Displays the user panel
    include_partial('tasks/task_user_info', array('application' => $application));
    ?>


    <div class="panel panel-default m-b-0">

     <div class="panel-heading">
              <h5 class="bug-key-title"><?php echo $application->getApplicationId(); ?></h5>
              <div class="panel-title">
                <?php
                  $q = Doctrine_Query::create()
                    ->from("ApForms a")
                    ->where("a.form_id = ?", $application->getFormId())
                      ->limit(1);
                  $form = $q->fetchOne();
                  if($form)
                  {
                      echo $form->getFormName();
                  }
                ?>
              </div>
       </div>

       <div class="panel-heading text-right">
            <a class="btn btn-primary btn-sm" id="printinvoice"
                href="/plan/applications/printinvoice/id/<?php echo $invoice->getId(); ?>"><i
                        class="fa fa-print"></i></a>
       </div>

      <div class="panel-body padding-0 panel-bordered" style="border-top:0;">
        <?php
            $html = "";

            try {
                $html = $invoice_manager->generate_invoice_template($invoice->getId(), false);
            } catch (Exception $ex) {
                error_log("Debug-t: Invoice Parse Error: " . $ex);

                $html = $invoice_manager->generate_invoice_template_old_parser($invoice->getId(), false);
            }

            echo $html;
        ?>
      </div>

    </div>
  </div>
</div>

</div>
