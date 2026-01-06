<div class="col-md-7 col-lg-8 col-xl-9">

	<div class="row">

		<div class="col-12">

			<!-- Tab Menu -->
			<nav class="user-tabs">
				<ul class="nav nav-tabs nav-tabs-bottom nav-justified">

					<li>
						<a class="nav-link active" href="#<?php echo $form->getFormName(); ?>" data-bs-toggle="tab"> <?php echo $form->getFormName(); ?></a>
					</li>


				</ul>
			</nav>
			<!-- /Tab Menu -->

			<!-- Tab Content -->
			<div class="tab-content">

				<!-- Active Content -->
				<div role="tabpanel" id="activeservice" class="tab-pane fade show active">

					<div class="row">

						<!-- here -->
						<div class="col-12 col-md-12 col-xl-12 d-flex">
							<div class="course-box blog grid-blog">
								<div class="course-content">

									<span class="course-title"><?php echo $form->getFormName() ?></span>
									<p><?php echo $form->getFormDescription() ?></p>
									<div class="row">
										<div class="col">
											<a href="/index.php/forms/view?id=<?php echo $form->getFormId(); ?>" class="btn btn-success"><i class="far fa-edit"></i> Submit </a>
										</div>

									</div>
								</div>
							</div>
						</div>
						<!-- end here -->





					</div>

				</div>
				<!-- /Active Content -->


			</div>
			<!-- /Tab Content -->


		</div>


	</div>

</div>