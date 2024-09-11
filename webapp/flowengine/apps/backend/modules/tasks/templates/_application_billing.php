<?php

/**
 * _viewinvoices.php partial.
 *
 * Displays any invoices attached to an application
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */
use_helper("I18N");
?>
<div class="panel panel-success">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __("Payment Details : " . $application->getApplicationId()); ?></h3>
    </div>

    <div class="panel-body padding-0">

        <table class="table table-special m-b-0">
            <thead>
                <?php
                $q = Doctrine_Query::create()
                    ->from("MfInvoice a")
                    ->where("a.app_id = ?", $application->getId())
                    ->orderBy("a.id DESC");
                $invoices = $q->execute();

                if (sizeof($invoices) > 0) {
                    ?>
                    <tr>
                        <th width="60px">#</th>
                        <th><?php echo __("Date Of Issue"); ?></th>
                        <th><?php echo __("Amount"); ?></th>
                        <th><?php echo __("Bill reference"); ?></th>
                        <th><?php echo __("Transaction ID"); ?></th>
                        <th><?php echo __("Status"); ?></th>
                        <th></th>
                    </tr>
                    <?php
                } else {
                    ?>
                    <tr>
                        <th>
                            <div align="left">
                                <?php echo __("No Payments Available"); ?>
                            </div>
                        </th>
                    </tr>
                    <?php
                }
                ?>
            </thead>
            <tbody>
                <?php
                //Iterate through any invoices attached to this application
                $invcount = 0;
                $invtotal = 0;

                $inv_currency = "";

                $invoice_manager = new InvoiceManager();

                foreach ($invoices as $invoice) {
                    $invcount++;

                    $inv_currency = $invoice->getCurrency();
                    ?>
                    <tr>
                        <td><?php echo $invoice->getId(); ?></td>
                        <td><?php echo $invoice->getCreatedAt(); ?></td>
                        <td><?php if ($invoice->getCurrency()) {
                            echo "KES";
                        } else {
                            echo "KES";
                        } ?>. <?php echo number_format($invoice->getTotalAmount()); ?></td>
                        <td><?php echo $invoice->getInvoiceNumber(); ?></td>
                        <td><?php echo $invoice->getTransactionId(); ?></td>
                        <td><?php echo $invoice->getStatus(); ?></td>
                        <td>
                            <a class="btn btn-success btn-xs" id="printinvoice"
                                href="/plan/invoices/view/id/<?php echo $invoice->getId(); ?>"><i
                                    class="fa fa-print"></i></a>

                            <?php
                            $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                            $sf_user->setAttribute('resume_url', $url);

                            if ($invoice->getPaid() == "1" && $sf_user->mfHasCredential('code_access_rights')) {
                                ?>
                                <a class="btn btn-success btn-xs" href="/plan/applications/paynow/id/<?php echo $invoice->getId();
                                ?>" id="printinvoice" type="button"><i class="fa fa-check mr5"></i>
                                    <?php echo __("Pay Now");
                                    ?></a>
                                <?php
                            }

                            if ($invoice->getPaid() <> 3 && $invoice->getPaid() <> 2 && $sf_user->mfHasCredential('code_access_rights')) {
                                ?>
                                <a title="Cancel Invoice" class="btn btn-danger btn-xs" id="makepayment"
                                    onClick="if(confirm('Are you sure you want to cancel this invoice?')){ return true; }else{ return false; }"
                                    href="/plan/applications/cancelpayment/id/<?php echo $invoice->getId(); ?>"><i
                                        class="fa fa-times mr5"></i> <?php echo __('Cancel Invoice'); ?></a>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                    if ($invoice->getPaid() == 2) {
                        $invtotal = $invtotal + $invoice->getTotalAmount();
                    }
                }

                if (sizeof($invoices) > 0) {
                    ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td colspan="5">
                            <p><?php echo __("Total Paid"); ?>     <?php echo $inv_currency . " " . number_format($invtotal); ?>
                            </p>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <?php
        if ($sf_user->mfHasCredential('addtopup') && !empty($task)) {
            ?>
            <table class="table table-special m-b-0">
                <thead>
                    <tr>
                        <td>
                            <div align="right">
                                <a class="btn btn-success btn-xs" id="addtopup" href="#" data-toggle="modal"
                                    data-target="#topupModal"><i class="fa fa-plus mr5"></i> <?php echo __('Top Up'); ?></a>
                            </div>
                        </td>
                    </tr>
                    </tbody>
            </table>

            <!-- Modal -->
            <div id="topupModal" class="modal fade" role="dialog">
                <div class="modal-dialog" style="width: 800px;">

                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">
                                <?php
                                echo __("Topup Invoice");
                                ?>
                            </h4>
                        </div>
                        <div class="modal-body">
                            <form class="form-bordered" id="topupform" method="post"
                                action="/plan/tasks/saveinvoice/id/<?php echo $task->getId(); ?>" autocomplete="off"
                                data-ajax="false">
                                <?php
                                $grandtotal = 0;
                                $q = Doctrine_Query::create()
                                    ->from('Invoicetemplates a')
                                    ->where('a.applicationform = ?', $application->getFormId())
                                    ->andWhere('a.applicationstage = ?', $application->getApproved());
                                $invoicetemplate = $q->fetchOne();

                                if ($invoicetemplate && $task->getStatus() != 25) {
                                    $q = Doctrine_Query::create()
                                        ->from('fee a')
                                        ->where('a.invoiceid = ?', $invoicetemplate->getId());
                                    $fixedfees = $q->execute();

                                    $count = 0;

                                    foreach ($fixedfees as $fee) {
                                        $count++;
                                        ?>
                                        <div class="form-group">
                                            <label class="col-sm-4">
                                                <?php echo $fee->getFeeCode(); ?>: <?php echo $fee->getDescription(); ?>
                                                <input type='hidden' name='feetitle[]'
                                                    value="<?php echo $fee->getFeeCode() . ": " . $fee->getDescription(); ?>">
                                            </label>
                                            <div class="col-sm-8">
                                                <input class="form-control" type='number' id='fee<?php echo $count; ?>'
                                                    name='feevalue[]' value="<?php echo number_format($fee->getAmount()); ?>"
                                                    onkeyup='updatefee()'>
                                            </div>
                                        </div>
                                        <?php
                                        $grandtotal = $grandtotal + $fee->getAmount();
                                    }
                                }

                                $feeselect = "<option>" . __("Choose Fee") . "</option>";

                                $q = Doctrine_Query::create()
                                    ->from("FeeCategory a")
                                    ->orderBy("a.id ASC");
                                $categories = $q->execute();

                                foreach ($categories as $category) {


                                    $feeselect .= "<optgroup label='--------------------------------------------------------------------------------------'></optgroup>";

                                    $feeselect .= "<optgroup label='" . $category->getTitle() . "'>";

                                    $q = Doctrine_Query::create()
                                        ->from("Fee a")
                                        ->where("a.fee_category = ?", $category->getId())
                                        ->orderBy("a.description ASC");
                                    $fees = $q->execute();
                                    foreach ($fees as $fee) {
                                        $feeselect .= "<option value='" . $fee->getId() . "'>" . $fee->getFeeCode() . ": " . $fee->getDescription() . "</option>";
                                    }

                                    $feeselect .= "</optgroup>";
                                }
                                ?>

                                <script language="javascript">
                                    function getFee(id, feecode) {
                                        var xmlHttpReq1 = false;
                                        var self1 = this;
                                        // Mozilla/Safari

                                        if (window.XMLHttpRequest) {
                                            self.xmlHttpReq1 = new XMLHttpRequest();
                                        }
                                        // IE
                                        else if (window.ActiveXObject) {
                                            self.xmlHttpReq1 = new ActiveXObject("Microsoft.XMLHTTP");
                                        }
                                        self.xmlHttpReq1.open('POST', '/plan/fees/getfee', true);
                                        self.xmlHttpReq1.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                        self.xmlHttpReq1.onreadystatechange = function () {
                                            if (self.xmlHttpReq1.readyState == 4) {
                                                if (document.getElementById(id).value != "") {
                                                    document.getElementById(id).value = self.xmlHttpReq1.responseText;
                                                    document.getElementById(id).disabled = false;
                                                    updateTotal();
                                                } else {
                                                    document.getElementById(id).disabled = true;
                                                }
                                            }
                                        }

                                        if (feecode == "Choose Fee") {
                                            document.getElementById(id).disabled = true;
                                        } else {
                                            self.xmlHttpReq1.send('code' + '=' + feecode);
                                        }
                                    }


                                    function updatefee() {
                                        updateTotal();
                                    }

                                    function updateTotal(amount) {
                                        var total = 0;

                                        $("#topupform input[type=text]").each(function () {
                                            if ($(this).attr('id') == 'servicefee' || $(this).attr('id') == 'totalfee') {

                                            } else {
                                                total = total + parseInt(this.value);
                                            }
                                        });

                                        $("#totalfee").val(total);
                                    }
                                </script>

                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_1", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_1' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>

                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_2", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_2' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>

                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_3", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_3' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>

                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_4", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_4' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>

                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_5", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_5' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>

                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_6", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_6' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>
                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_7", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_7' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>
                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_8", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_8' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>
                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_9", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_9' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>
                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_10", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_10' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>

                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_11", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_11' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>

                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_12", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_12' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>

                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_13", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_13' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>

                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_14", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_14' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>

                                <div class='form-group' class='formgroup'>
                                    <label class='col-sm-4'>
                                        <select name='feetitle[]' class='form-control'
                                            onChange='getFee("inv_15", this.value)'>
                                            <?php echo $feeselect; ?>
                                        </select>
                                    </label>
                                    <div class='col-sm-8'> <input type='number' id='inv_15' onkeyup='updateTotal();'
                                            name='feevalue[]' class='form-control' value="0" /></div>
                                </div>

                                <input type='hidden' id='totalfee' name='feevalue[]'
                                    value="<?php echo number_format($grandtotal); ?>">

                                <div class="form-group">
                                    <div class="col-sm-12" style="padding: 10px;" align="right">
                                        <button class="btn btn-primary" type="submit" name="submitbuttonname"
                                            value="submitbuttonvalue"> <?php echo __("Submit"); ?> </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            <?php
        }
        ?>

    </div>
</div>