<div style="float:left;">
		<ul class="breadcrumb">
					<li><a href="#">Invoices</a></li>
					<li><a href="#">Unconfirmed Payments</a></li>
		</ul>
	</div>

<div class="g12 nodrop">
			<h1>Invoices</h1>
		</div>	

<div class="g12">
			
			<table class="datatable">
				<thead>
					<tr>
                     <th>Invoice</th><th>Application</th><th>Submitted On</th><th>Status</th><th style="background: none;">Actions</th>
					</tr>
				</thead>
				<tbody>
				<?php
						foreach($applications as $application)
						{
							$invoices = $application->getMfInvoice();
							if($invoices)
							{
								foreach($invoices as $invoice)
								{
				?>
									<tr>
										<td><?php echo $invoice->getInvoiceNumber(); ?></td>
										<td><?php echo $application->getApplicationId(); ?></td>
										<td><?php echo $invoice->getCreatedAt(); ?></td>
										<td>
										<?php
											if($invoice->getPaid() == "0")
											{
												echo "Not Paid";
											}
											else if($invoice->getPaid() == "15")
											{
												echo "Confirmed Payment";
											}
											else
											{
												echo "Paid";
											}
										?>
										</td>
										<td>
										<a title='View Application' href='<?php echo public_path('backend.php/invoices/view/id/'.$invoice->getId()); ?>'><img src='<?php echo public_path('assets_backend/images/icons/dark/create_write.png') ?>'></a>
										</td>
									</tr>
				<?php
								}
							}
					}
				?>
				</tbody>
			</table>
		</div>