<div class="header">
			
				<!-- Logo -->
                <div class="header-left">
                    <a href="/" class="logo">
						<img src="/asset_mentor/admin/assets/img/logo2.png" alt="Admin Portal - Uasin Gishu County">
					</a>
					<a href="/" class="logo logo-small">
						<img src="/asset_mentor/admin/assets/img/logo-small.png" alt="Admin Portal - Uasin Gishu County" width="30" height="30">
					</a>
                </div>
				<!-- /Logo -->
				
				<a href="javascript:void(0);" id="toggle_btn">
					<i class="fe fe-text-align-left"></i>
				</a>
				
				<div class="top-nav-search">
					<form action="/plan/applications/search" method="post">
						<input type="text" class="form-control" placeholder="Search here">
						<button class="btn" type="submit"><i class="fa fa-search"></i></button>
					</form>
				</div>
				
				<!-- Mobile Menu Toggle -->
				<a class="mobile_btn" id="mobile_btn">
					<i class="fa fa-bars"></i>
				</a>
				<!-- /Mobile Menu Toggle -->
				
				<!-- Header Right Menu -->
				<ul class="nav user-menu">

					<!-- Notifications -->
					<li class="nav-item dropdown noti-dropdown">
						<a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
							<i class="fe fe-bell"></i> <span class="badge badge-pill">3</span>
						</a>
						<div class="dropdown-menu notifications">
							<div class="topnav-dropdown-header">
								<span class="notification-title">Your Notifications</span>
								<a href="javascript:void(0)" class="clear-noti"> Clear All </a>
							</div>
							<div class="noti-content">
								<ul class="notification-list">
									<li class="notification-message">
										<a href="#">
											<div class="media d-flex">
												<span class="avatar avatar-sm flex-shrink-0">
												<?php
                                                    if($logged_reviewer->getProfilePic())
                                                    {
                                                        ?>
                                                        <img src="<?php echo $site_settings->getUploadDirWeb(); ?><?php echo $logged_reviewer->getProfilePic(); ?>" alt="" />
                                                        <?php
                                                    }
                                                    else
                                                    {
                                                        ?>
                                                        <img class="avatar-img rounded-circle" alt="User Image" src="/asset_mentor/admin/assets/img/user/user.jpg">
                                                                                            
                                                        <?php
                                                    }
                                                    ?>
												</span>
												<div class="media-body flex-grow-1">
													<p class="noti-details"><span class="noti-title"><?php echo $logged_reviewer->getStrfirstname()." ".$logged_reviewer->getStrlastname(); ?></span></p>
													<p class="noti-time"><span class="notification-time">4 mins ago</span></p>
												</div>
											</div>
										</a>
									</li>
																	
								</ul>
							</div>
							<div class="topnav-dropdown-footer">
								<a href="#">View all Notifications</a>
							</div>
						</div>
					</li>
					<!-- /Notifications -->
					
					<!-- User Menu -->
					<li class="nav-item dropdown has-arrow">
						<a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
							<span class="user-img"><img class="rounded-circle" src="/asset_mentor/admin/assets/img/profiles/avatar-12.jpg" width="31" alt="Ryan Taylor"></span>
						</a>
						<div class="dropdown-menu">
							<div class="user-header">
								<div class="avatar avatar-sm">
								<?php
                                                    if($logged_reviewer->getProfilePic())
                                                    {
                                                        ?>
                                                        <img src="<?php echo $site_settings->getUploadDirWeb(); ?><?php echo $logged_reviewer->getProfilePic(); ?>" alt="" />
                                                        <?php
                                                    }
                                                    else
                                                    {
                                                        ?>
                                                        <img class="avatar-img rounded-circle" alt="User Image" src="/asset_mentor/admin/assets/img/user/user.jpg">
                                                                                            
                                                        <?php
                                                    }
                                                    ?></div>
								<div class="user-text">
									<h6><?php echo $logged_reviewer->getStrfirstname() ?></h6>
									<p class="text-muted mb-0"><?php echo $logged_reviewer->getStrfirstname()." ".$logged_reviewer->getStrlastname(); ?></p>
								</div>
							</div>
							<a class="dropdown-item" href="/plan/dashboard/profile">My Profile</a>
							<a class="dropdown-item" href="/plan/login/logout">Logout</a>
						</div>
					</li>
					<!-- /User Menu -->
					
				</ul>
				<!-- /Header Right Menu -->
				
            </div>
			<!-- /Header -->