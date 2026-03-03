<?php

use_helper("I18N");
$invoice_manager = new InvoiceManager();
$otb_helper = new OTBHelper();

?>

<div class="panel panel-success mb0">
	<div class="panel-heading">
	  <h2 style="color: white;"><i class="fa fa-home"></i><?php echo __('Get Estimate'); ?></h2>
	</div>
</div>

<div class="pageheader">
  <!--<h2><i class="fa fa-home"></i><?php echo __('Get Estimate'); ?></h2>-->
  <div class="breadcrumb-wrapper">
    <ol class="breadcrumb">
      <li><a href="<?php echo url_for('@homepage') ?>"><?php echo __('Home'); ?></a></li>
      <li class="active"><?php echo __('Get Estimate'); ?></li>
    </ol>
  </div>
</div>

<div class="contentpanel">
	<div class="panel panel-bordered radius-all">
		<div class="panel-body panel-body-nopadding">
						   <form method="post">
							<select size="1" class='form-control' name="table2_length" aria-controls="table2" class="select2" onChange="window.location='/plan/calculator/index/filter/' + this.value;">
								<option value="0" selected="selected">Select Product/Service</option>
								<?php
								$user_category=$sf_user->getGuardUser()->getProfile()->getRegisteras();
								$categories=Doctrine_Core::getTable('SfGuardUserCategoriesForms')->findByCategoryid($user_category);
								//Get Forms allowed
								$allowed_forms=array();
								foreach($categories as $c){
									$allowed_forms[]=$c['formid'];
								}

								$q = Doctrine_Query::create()
										->from('ApForms a')
										->where('a.form_id <> 15 AND a.form_id <> 16 AND a.form_id <> 17 AND  a.form_id <> 6 AND a.form_id <> 7')
										->andWhere('a.form_active = 1 AND a.form_type = 1')
										->andWhereIn('a.form_id',$allowed_forms)
										->orderBy('a.form_name ASC');
								$applicationforms = $q->execute();
								foreach($applicationforms as $applicationform)
								{
									$selected = "";
									if($applicationform->getFormId() == $filter)
									{
										$selected = "selected='selected'";
										$main_currency = $applicationform->getPaymentCurrency();
									}

									echo "<option value='".$applicationform->getFormId()."' ".$selected.">".$applicationform->getFormCode()." - ".$applicationform->getFormName()."</option>";
								}
								?>
					  		</select>
							<br/>
							<!--<input type="submit" value="Get Estimate"/>-->
						   </form>
							<?php if($appform){
								$output = "<h2>Estimated amount to pay: <br/></h2>";

								$computed_fees = $invoice_manager->calculateEstimate($application_form, $appform);

								foreach ($computed_fees as $fee_obj){

										$output .= "<h3>".$fee_obj['title']." Estimate = ";

										$output .= sfConfig::get('app_currency')." ".$fee_obj['total_amount']."<br/></h3>";

								}

								echo $output;
								
							} ?>

							<?php if($filter){?>
							<form method="post" class="form-horizontal form-bordered top_label">
								<?php
								$element_ids = $invoice_manager->getEstimationInputFields($filter);
								$fees_exist = array_shift($element_ids);
								if($fees_exist == 'yes'){
									$fee_fields_markup = "<input type='hidden' name='appform' value='".$filter."'/>";
									if($element_ids){
										$fee_fields_markup .= $otb_helper->get_fields_html_markup($filter, $element_ids);
									}
									echo $fee_fields_markup;
								 ?>
								 <br/>
								 <input type="submit" class="btn btn-primary" value="Get Estimate"/>
								 <?php }else{ ?>
									<div class='panel panel-success'><h2 class='panel-title'><?php echo _('We are currently updating the fees in the county finance bill for this service. Kindly try again later'); ?>.</h2></div>;
								 <?php } ?>
							</form>
							<?php } ?>



		</div>
	</div>
</div>