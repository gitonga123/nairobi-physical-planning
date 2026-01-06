<?php
use_helper("I18N");
?>
<div class="contentpanel">
    <div class="panel panel-default">
      <div class="panel-heading">
          <h3 class="panel-title"><?php echo __('Fixed Charges'); ?></h3>
          <a href="/backend.php/services" class="btn btn-primary pull-right" style="margin-top: -26px;">Back to Services</a>
      </div>


    <form action="/backend.php/services/saveotherfees/id/<?php echo $service->getId(); ?>" method="post">

        <div class="panel-body">

            <div class="form-group">
                <label class="col-sm-4">
                    Description
                </label>
                <div class="col-sm-4"> 
                    Amount
                </div>
                <div class="col-sm-2"> 
                    First submissions only?
                </div>
                <div class="col-sm-2"> 
                    Renewals only?
                </div>
            </div>

            <?php 
            $count = 0;
            foreach($fees as $fee){ 
            ?>
            <div class="form-group">
                <label class="col-sm-4">
                    <input type="text" id="other_fees" name="other_fees_code[]" class="form-control" value="<?php echo $fee->getServiceCode(); ?>" placeholder="Fee Title">
                </label>
                <div class="col-sm-4"> 
                    <input type="number" id="other_fees" name="other_fees_amount[]" class="form-control" value="<?php echo $fee->getAmount(); ?>">
                </div>
                <div class="col-sm-2"> 
                    <input type="checkbox" name="other_fees_first_time_<?php echo $count; ?>" <?php if($fee->getAsFirstSubmissionFee()){ ?>checked="checked"<?php } ?>>
                </div>
                <div class="col-sm-2"> 
                    <input type="checkbox" name="other_fees_renewal_<?php echo $count; ?>" <?php if($fee->getAsRenewalFee()){ ?>checked="checked"<?php } ?>>
                </div>
            </div>
            <?php 
                $count++;
            }
            ?>

            <div class="form-group">
                <label class="col-sm-4">
                    <input type="text" id="other_fees" name="other_fees_code[]" class="form-control" placeholder="Fee Title">
                </label>
                <div class="col-sm-4"> 
                    <input type="number" id="other_fees" name="other_fees_amount[]" class="form-control" value="0">
                </div>
                <div class="col-sm-2"> 
                    <input type="checkbox" name="other_fees_first_time_<?php echo $count++; ?>" value="1">
                </div>
                <div class="col-sm-2"> 
                    <input type="checkbox" name="other_fees_renewal_<?php echo $count; ?>" value="1">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4">
                    <input type="text" id="other_fees" name="other_fees_code[]" class="form-control" placeholder="Fee Title">
                </label>
                <div class="col-sm-4"> 
                    <input type="number" id="other_fees" name="other_fees_amount[]" class="form-control" value="0">
                </div>
                <div class="col-sm-2"> 
                    <input type="checkbox" name="other_fees_first_time_<?php echo $count++; ?>" value="1">
                </div>
                <div class="col-sm-2"> 
                    <input type="checkbox" name="other_fees_renewal_<?php echo $count; ?>" value="1">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4">
                    <input type="text" id="other_fees" name="other_fees_code[]" class="form-control" placeholder="Fee Title">
                </label>
                <div class="col-sm-4"> 
                    <input type="number" id="other_fees" name="other_fees_amount[]" class="form-control" value="0">
                </div>
                <div class="col-sm-2"> 
                    <input type="checkbox" name="other_fees_first_time_<?php echo $count++; ?>" value="1">
                </div>
                <div class="col-sm-2"> 
                    <input type="checkbox" name="other_fees_renewal_<?php echo $count; ?>" value="1">
                </div>
            </div>

        </div>

        <div class="panel-footer">
            <button type="submit" class="btn btn-primary">Save Details</button>
        </div>

        </form>
    </div>
</div>
