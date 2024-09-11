<div class="sidebar" id="sidebar">
                <div class="sidebar-inner slimscroll">
					<div id="sidebar-menu" class="sidebar-menu">
						<ul>
							<li class="menu-title"> 
								<span><i class="fe fe-home"></i>Get Started</span>
							</li>
							<li class="active"> 
								<a href="/plan/dashboard"><span>Your Dashboard</span></a>
							</li>
							<?php if($sf_user->mfHasCredential("access_applications")): ?>
							<li> 
								<a href="/plan/dashboard/applications"><span>Submissions</span></a>
							</li>
							<?php endif; ?>
							<li> 
								<a href="mentee.html"><span>Applicants</span></a>
							</li>
							<li> 
								<a href="booking-list.html"><span>Invoices</span></a>
							</li>
							<li> 
								<a href="categories.html"><span>Permits</span></a>
							</li>
							<li> 
								<a href="transactions-list.html"><span>Applicant Feedback</span></a>
							</li>
							
							<li class="submenu">
								<a href="#"><span> Reporting</span> <span class="menu-arrow"></span></a>
								<ul style="display: none;">
									<li><a href="invoices.html">Standard Dashboard</a></li>
									<li><a href="invoice-grid.html">Management Reports</a></li>
									
								</ul>
							</li>
																		
							<li class="submenu">
								<a href="#"><span> Admin Settings </span> <span class="menu-arrow"></span></a>
								<ul style="display: none;">
									
									<li class="submenu">
										<a href="#"><span>Manage Content</span> <span class="menu-arrow"></span></a>
										<ul style="display: none;">
											<li><a href="invoices.html">Pages</a></li>
											<li><a href="invoice-grid.html">Image Banners</a></li>
											<li><a href="invoice-grid.html">News pages</a></li>
											<li><a href="invoice-grid.html">FAQ Information</a></li>
											<li><a href="invoice-grid.html">Alerts</a></li>
											
										</ul>
									</li>
									
									<li><a href="form-input-groups.html">Workflows</a></li>
									<li><a href="form-horizontal.html">System Security</a></li>
									
									<li class="submenu"><a href="#"> 
									Additional Settings
									 </a>
									 <ul style="display: none;">
											<li><a href="invoices.html">Forms & Categories</a></li>
											<li><a href="invoice-grid.html">View All Forms</a></li>
											<li><a href="invoice-grid.html">Fees</a></li>
											<li><a href="invoice-grid.html">Fee Categories</a></li>
											<li><a href="invoice-grid.html">Merchants Providers</a></li>
											<li><a href="invoice-grid.html">Currencies</a></li>
											<li><a href="invoice-grid.html">Fee Codes</a></li>
											<li><a href="invoice-grid.html">Fee & Zones</a></li>
											<li><a href="invoice-grid.html">Approved Membership</a></li>
											<li><a href="invoice-grid.html">Department Agencies</a></li>
											<li><a href="invoice-grid.html">Plots</a></li>
											<li><a href="invoice-grid.html">Invoice Templates</a></li>
											<li><a href="invoice-grid.html">Agenda Layout</a></li>
											
											
											
										</ul>
									 </li>
									 <li><a href="form-vertical.html"> Site Configs </a></li>
									
								</ul>
							</li>
							
							
						</ul>
					</div>
                </div>
            </div>