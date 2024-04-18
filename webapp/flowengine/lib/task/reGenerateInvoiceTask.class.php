<?php

class reGenerateInvoiceTask extends sfBaseTask
{
  protected function configure()
  {
    // add your own arguments here
    $this->addArguments(array(
      new sfCommandArgument('invoice', sfCommandArgument::REQUIRED, 'The invoice id to regenerate.'),
      new sfCommandArgument('applicationId', sfCommandArgument::OPTIONAL, 'The application id to regenerate.'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'Re-Generate an invoice'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'permitflow';
    $this->name             = 'reGenerateInvoice';
    $this->briefDescription = 'Regenerate a invoice with the wrong amount, it cancels and generates a new one';
    $this->detailedDescription = <<<EOF
The [reGenerateInvoice|INFO] task does things.
Call it with:

  [php symfony permitflow:reGenerateInvoice|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $this->logSection('permitflow', 'Start the application task', 0, "INFO");
    $invoice_id = $arguments['invoice'];
    $invoice = Doctrine_Query::create()->from('MfInvoice m')->where('m.id = ?', $invoice_id)->fetchOne();
    $applicationM = new ApplicationManager();
    if ($invoice) {
      $this->logSection('permitflow', 'Cancel the invoice ...', 0, "info");
      Doctrine_Query::create()->UPDATE('MfInvoice m')->SET('m.paid', '?', 3)->where('m.id = ?', $invoice_id)->execute();
      $this->logSection('permitflow', 'generate a fresh invoice ...', 0, "info");

      $newInvoice = $applicationM->update_invoices($invoice->getAppId());

      $this->logSection('permitflow', 'Invoice Created successfully ...', 0, "INFO");
      $this->logSection('permitflow', $newInvoice->getId());
    } else {
      $application = $arguments['applicationId'];
      $applicationD = Doctrine_Query::create()->from('FormEntry m')->where('m.id = ?', $application)->fetchOne();

      if ($applicationD) {

        $newInvoice = $applicationM->update_invoices($application);

        if (is_null($newInvoice) || empty($newInvoice)) {
          $this->logSection('permitflow', 'Invoice Failed ...', 0, "Error");
          $this->logSection('permitflow', $newInvoice);
        } else {
          $this->logSection('permitflow', 'Invoice Created successfully ...', 0, "INFO");
          $this->logSection('permitflow', $newInvoice->getId());
        }
      } else {
        $this->logSection('permitflow', 'Invoice Not Found ...', 0, "ERROR");
      }
    }
  }
}
