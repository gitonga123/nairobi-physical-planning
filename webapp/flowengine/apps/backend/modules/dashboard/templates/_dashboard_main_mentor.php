<div class="page-wrapper">
			
                <div class="content container-fluid">
					
					<!-- Page Header -->
					<div class="page-header">
						<div class="row">
							<div class="col-sm-12">
								<h3 class="page-title">Kisii County Physical Planning Admin Portal. Welcome , - <?php echo $user->getStrfirstname()." ".$user->getStrlastname() ?></h3>
								<ul class="breadcrumb">
									<li class="breadcrumb-item active">Access your Dashboard & much more...</li>
								</ul>
							</div>
						</div>
					</div>
					<!-- /Page Header -->

					<div class="row">
						<div class="col-xl-3 col-sm-6 col-12">
							<div class="card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon text-primary border-primary">
											<i class="fe fe-folder"></i>
										</span>
										<div class="dash-count">
											<h3><?php echo $my_tasks ?></h3>
										</div>
									</div>
									<div class="dash-widget-info">
										<h6 class="text-muted">Assigned Tasks</h6>
										<div class="progress progress-sm">
											<div class="progress-bar bg-primary w-<?php echo $my_tasks ?>"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-3 col-sm-6 col-12">
							<div class="card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon text-success">
											<i class="fe fe-folder"></i>
										</span>
										<div class="dash-count">
											<h3><?php echo $completed_tasks ?></h3>
										</div>
									</div>
									<div class="dash-widget-info">
										
										<h6 class="text-muted">Completed Tasks</h6>
										<div class="progress progress-sm">
											<div class="progress-bar bg-success w-<?php echo $completed_tasks ?>"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-3 col-sm-6 col-12">
							<div class="card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon text-danger border-danger">
											<i class="fe fe-star-o"></i>
										</span>
										<div class="dash-count">
											<h3><?php echo $new_messages ?> </h3>
										</div>
									</div>
									<div class="dash-widget-info">
										
										<h6 class="text-muted">Inbox</h6>
										<div class="progress progress-sm">
											<div class="progress-bar bg-danger w-<?php echo $new_messages ?>"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-3 col-sm-6 col-12">
							<div class="card">
								<div class="card-body">
									<div class="dash-widget-header">
										<span class="dash-widget-icon text-warning border-warning">
											<i class="fe fe-users"></i>
										</span>
										<div class="dash-count">
											<h3><?php echo $applicants ?> </h3>
										</div>
									</div>
									<div class="dash-widget-info">
										
										<h6 class="text-muted">Applicants</h6>
										<div class="progress progress-sm">
											<div class="progress-bar bg-warning w-<?php echo $applicants ?>"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

				
					<!-- table -->
					<div class="row">
						<div class="col-sm-12">
							<div class="card">
							<div class="card-header">
									<h4 class="card-title">Submitted Applications List</h4>
								</div>
								<div class="card-body">
									<div class="table-responsive">
										<table class="datatable table table-hover table-center mb-0">
											<thead>
												<tr>
													<th>Application No </th>											
													<th>Date of Submission</th>
													<th>Applicant</th>
													<th class="text-center">Status</th>
													<th class="text-center">Actions</th>
												</tr>
											</thead>
											<tbody>
											<?php foreach($current_paginator as $apps): ?>
												<tr>
													<td><a href="#"><?php echo $apps ->getApplicationId() ?></a></td>
													
													<td>
														<h2 class="table-avatar">
															<a href="profile.html" class="avatar avatar-sm me-2"><img class="avatar-img rounded-circle" src="assets/img/user/user.jpg" alt="User Image"></a>
															<a href="profile.html">Jonathan Doe </a>
														</h2>
													</td>
													<td>$100.00</td>
													<td class="text-center">
														<span class="badge badge-pill bg-success inv-badge">Paid</span>
													</td>
													<td class="text-end">
														<div class="actions">
															<a data-bs-toggle="modal" href="#edit_invoice_report" class="btn btn-sm bg-success-light me-2">
																<i class="fe fe-pencil"></i> Edit
															</a>
															<a class="btn btn-sm bg-danger-light" data-bs-toggle="modal" href="#delete_modal">
																<i class="fe fe-trash"></i> Delete
															</a>
														</div>
													</td>
												</tr>	
												<?php endforeach; ?>																						
										</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>			
					</div>
				</div>			
			</div>
					<!-- end table --> 
					
				</div>			
			</div>