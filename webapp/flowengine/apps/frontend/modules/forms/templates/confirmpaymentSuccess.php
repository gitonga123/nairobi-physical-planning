<?php
use_helper('I18N');

try
{
  $_SESSION['partial_amount'] = false;
  $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

  $application_manager = new ApplicationManager();
  $payments_manager = new PaymentsManager();
  $invoice_manager = new InvoiceManager();

?>
<div class="contentpanel">
  <div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <div class="panel-btns">
              <a href="" class="panel-close">×</a>
              <a href="" class="minimize">−</a>
            </div><!-- panel-btns -->
            <h4 class="panel-title"><?php echo __('Payment') ?></h4>
            <p><?php echo __('Thank you for using our payment services') ?></p>
          </div><!-- panel-heading -->
          <div class="panel-body">
            <?php
              $invoice = $invoice_manager->get_invoice_by_id($sf_user->getAttribute('invoice_id'));

              if($payments_manager->authorize_validation($sf_user->getAttribute('invoice_id')))
              {
				  error_log('--------authorize_validation---------');
                if($payments_manager->process_validation($sf_user->getAttribute('invoice_id'), $_REQUEST))
                {
				  error_log('--------process_validation---------');
                   //Check invoice status and display notifications/messages
                   if($invoice->getPaid() == 2)
                   {
                      $redirect_to_service = false;
                      $service_id = 0;

                      //If there is a permit that requires signing and attaching then redirect to the permit
                      $application = $invoice->getFormEntry();

                      if ($application_manager->is_draft($application->getFormId(), $application->getEntryId()) && !$invoice_manager->has_unpaid_invoice($application->getId())) {
                         $application = $application_manager->publish_draft($application->getId());
                      }

                      if (!$application_manager->is_draft($application->getFormId(), $application->getEntryId()) && !$invoice_manager->has_unpaid_invoice($application->getId())) {
                         $application_manager->update_services($application->getId());
                      }

                      $q = Doctrine_Query::create()
                         ->from("SavedPermit a")
                         ->where("a.application_id = ?", $application->getId());
                      $permits = $q->execute();

                      error_log("Permits: ".sizeof($permits));

                      foreach($permits as $permit)
                      {
                        $q = Doctrine_Query::create()
                           ->from("Permits a")
                           ->where("a.id = ?", $permit->getTypeId())
                           ->andWhere("a.parttype = ?", 2);
                        error_log("Templates: ".$q->count());
                        if($q->count())
                        {
                          $redirect_to_service = true;
                          $service_id = $permit->getId();
                        }
                      }

                      if($redirect_to_service)
                      {
                        //If no permit needs to be signed then just redirect to the application
                        $link = "/plan/permits/create/id/".$service_id;
                        ?>
                          <div class="alert alert-success" align="center">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <strong><?php echo __('Successful Payment!') ?></strong><?php echo __('You are being redirected to your service. You will be given a link for you to download and attach a signed copy of your application.....') ?> <br>
                            <a href="<?php echo $link; ?>" class="alert-link"><?php echo __('Click here to download and reattach a signed copy of your application') ?></a>
                          </div>
                          <script type="text/javascript">
                          function Redirect()
                          {
                            window.top.location.href="<?php echo $link; ?>";
                          }
                          setTimeout('Redirect()', 5000);
                          </script>
                          <?php
                      }
                      else
                      {
                      //If no permit needs to be signed then just redirect to the application
                      $link = "/plan/application/view/id/".$invoice->getFormEntry()->getId();
                      ?>
                        <div class="alert alert-success" align="center">
                          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                          <strong><?php echo __('Successful Payment!') ?></strong> <?php echo __('You are being redirected to your service.....') ?> <br>
                          <a href="<?php echo $link; ?>" class="alert-link"><?php echo __('Click here to view your service') ?></a>
                        </div>
                        <script type="text/javascript">
                        function Redirect()
                        {
                          window.top.location.href="<?php echo $link; ?>";
                        }
                        setTimeout('Redirect()', 5000);
                        </script>
                        <?php
                      }
                   }
                   elseif($invoice->getPaid() == 3)
                   {
                      $link = "/plan/invoices/view/id/".$invoice->getId();
                      ?>
                      <div class="alert alert-danger" align="center">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <strong><?php echo __('Failed Payment!') ?></strong> <?php echo __('You are being redirected to your invoice.....') ?> <br>
                        <a href="<?php echo $link; ?>" class="alert-link"><?php echo __('Click here to view your invoice') ?></a>
                      </div>
                      <script type="text/javascript">
                      function Redirect()
                      {
                        window.top.location.href="<?php echo $link; ?>";
                      }
                      setTimeout('Redirect()', 5000);
                      </script>
                      <?php
                   }
                   else
                   {
                      $link = "/plan/application/view/id/".$invoice->getFormEntry()->getId();
                      ?>
                      <div class="alert alert-info" align="center">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <strong><?php echo __('Payment is awaiting confirmation!') ?></strong> <?php echo __('It may take a few minutes to confirm your payment.') ?> <br> <?php echo __('You are being redirected to your service.....') ?><br>
                        <a href="<?php echo $link; ?>" class="alert-link"><?php echo __('Click here to view your service') ?></a>
                      </div>
                      <script type="text/javascript">
                      function Redirect()
                      {
                        window.top.location.href="<?php echo $link; ?>";
                      }
                      setTimeout('Redirect()', 5000);
                      </script>
                      <?php
                   }
                }
                else
                {
					error_log('------------- false process_validation----------');
                    $link = "/plan/application/view/id/".$invoice->getFormEntry()->getId();
                      ?>
                      <div class="alert alert-info" align="center">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <strong><?php echo __('Payment is awaiting confirmation!') ?></strong> <?php echo __('It may take a few minutes to confirm your payment') ?> <br> <?php echo __('You are being redirected to your service.....') ?> <br>
                        <a href="<?php echo $link; ?>" class="alert-link"><?php echo __('Click here to view your service') ?></a>
                      </div>
                      <script type="text/javascript">
                      function Redirect()
                      {
                        window.top.location.href="<?php echo $link; ?>";
                      }
                      setTimeout('Redirect()', 2000);
                      </script>
                      <?php
                }
              }
              else
              {
					error_log('------------- false authorize_validation----------');
                    $link = "/plan/application/view/id/".$invoice->getFormEntry()->getId();
                      ?>
                      <div class="alert alert-info" align="center">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <strong><?php echo __('Payment is awaiting confirmation!') ?></strong> <?php echo __('It may take a few minutes to confirm your payment.') ?> <br> <?php echo __('You are being redirected to your service.....') ?> <br>
                        <a href="<?php echo $link; ?>" class="alert-link"><?php echo __('Click here to view your service') ?></a>
                      </div>
                      <script type="text/javascript">
                      function Redirect()
                      {
                        window.top.location.href="<?php echo $link; ?>";
                      }
                      setTimeout('Redirect()', 2000);
                      </script>
                      <?php
              }

              $sf_user->setAttribute('invoice_id', 0);
            ?>
        </div>
      </div><!-- panel -->
    </div>
  </div>
</div>
<?php
}catch(Exception $ex)
{
  error_log("Payment-Redirect-Error: Error on redirection from payment checkout ".$ex);

  ?>
  <div class="contentpanel">
    <div class="row">
      <div class="col-md-12">
          <div class="panel panel-default">
            <div class="panel-heading">
              <div class="panel-btns">
                <a href="" class="panel-close">×</a>
                <a href="" class="minimize">−</a>
              </div><!-- panel-btns -->
              <h4 class="panel-title"><?php echo __('Payment') ?></h4>
              <p><?php echo __('Thank you for using our payment services') ?></p>
            </div><!-- panel-heading -->
            <div class="panel-body">
              <div class="alert alert-info" align="center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong><?php echo __('Payment is awaiting confirmation!')?></strong> <?php echo __('It may take a few minutes to confirm your payment') ?> <br>
              </div>
            </div>
          </div><!-- panel -->
        </div>
      </div>
    </div>
  <?php
}
?>
