<div class="content">
  <ul class="breadcrumb">
    <li><a href="/index.php/">Home</a> <span class="divider">/</span></li>
    <li class="active">Membership Verification</li>
  </ul>    <!-- Docs nav
  ================================================== -->
  <div class="row">
    <div class="span12" >
      <!-- Download
      ================================================== -->
      <section id="content-container">
        <div class="span4 offset4 well2 padded-20" >
          <legend>Membership Verification</legend>
          <div class="table-box">
            <?php if ($valid): ?>
            <div class="alert alert-info">
              <h4 class="alert-heading">You have authorized the use of your membership account on this system.</h4>
              You may now login and submit an application for review and approval.
            </div>
            <?php else: ?>
            <div class="alert alert-error">
              <h4 class="alert-heading">Verification not valid</h4>
              The verification link is not valid. This might be due to the verification has already been confirm. If so login.
            </div>
            <?php endif;?>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
<!-- <section class="comp-section comp-cards">
  <div class="section-header">
    <h3 class="section-title">Cards</h3>
    <div class="line"></div>
  </div>
  <div class="row">
    <div class="col-12 col-md-6 col-lg-4 d-flex">
      <div class="card flex-fill">
        <img alt="Card Image" src="assets/img/img-01.jpg" class="card-img-top">
        <div class="card-header">
          <h5 class="card-title mb-0">Card with image and links</h5>
        </div>
        <div class="card-body">
          <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
          <a class="card-link" href="#">Card link</a>
          <a class="card-link" href="#">Another link</a>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4 d-flex">
      <div class="card flex-fill">
        <img alt="Card Image" src="assets/img/img-01.jpg" class="card-img-top">
        <div class="card-header">
          <h5 class="card-title mb-0">Card with image and button</h5>
        </div>
        <div class="card-body">
          <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
          <a class="btn btn-primary" href="#">Go somewhere</a>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4 d-flex">
      <div class="card flex-fill">
        <img alt="Card Image" src="assets/img/img-01.jpg" class="card-img-top">
        <div class="card-header">
          <h5 class="card-title mb-0">Card with image and list</h5>
        </div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item">Cras justo odio</li>
          <li class="list-group-item">Dapibus ac facilisis in</li>
          <li class="list-group-item">Vestibulum at eros</li>
        </ul>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-12 col-md-6 col-lg-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0">Card with links</h5>
        </div>
        <div class="card-body">
          <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
          <a class="card-link" href="#">Card link</a>
          <a class="card-link" href="#">Another link</a>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0">Card with button</h5>
        </div>
        <div class="card-body">
          <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
          <a class="btn btn-primary" href="#">Go somewhere</a>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0">Card with list</h5>
        </div>
        <ul class="list-group list-group-flush">
          <li class="list-group-item">Cras justo odio</li>
          <li class="list-group-item">Dapibus ac facilisis in</li>
          <li class="list-group-item">Vestibulum at eros</li>
        </ul>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-12 col-md-6 col-lg-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          This is my header
        </div>
        <div class="card-body">
          <h5 class="card-title">Special title treatment</h5>
          <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
        </div>
        <div class="card-footer text-muted">
          This is my footer
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <ul role="tablist" class="nav nav-tabs card-header-tabs float-end">
            <li class="nav-item">
              <a href="#tab-1" data-bs-toggle="tab" class="nav-link active">Active</a>
            </li>
            <li class="nav-item">
              <a href="#tab-2" data-bs-toggle="tab" class="nav-link">Link</a>
            </li>
            <li class="nav-item">
              <a href="#tab-3" data-bs-toggle="tab" class="nav-link disabled">Disabled</a>
            </li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content pt-0">
            <div role="tabpanel" id="tab-1" class="tab-pane fade show active">
              <h5 class="card-title">Card with tabs</h5>
              <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
              <a class="btn btn-primary" href="#">Go somewhere</a>
            </div>
            <div role="tabpanel" id="tab-2" class="tab-pane fade text-center">
              <h5 class="card-title">Card with tabs</h5>
              <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
              <a class="btn btn-primary" href="#">Go somewhere</a>
            </div>
            <div role="tabpanel" id="tab-3" class="tab-pane fade">
              <h5 class="card-title">Card with tabs</h5>
              <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
              <a class="btn btn-primary" href="#">Go somewhere</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <ul role="tablist" class="nav nav-pills card-header-pills float-end">
            <li class="nav-item">
              <a href="#tab-4" data-bs-toggle="tab" class="nav-link active">Active</a>
            </li>
            <li class="nav-item">
              <a href="#tab-5" data-bs-toggle="tab" class="nav-link">Link</a>
            </li>
            <li class="nav-item">
              <a href="#tab-6" data-bs-toggle="tab" class="nav-link disabled">Disabled</a>
            </li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content pt-0">
            <div role="tabpanel" id="tab-4" class="tab-pane fade show active">
              <h5 class="card-title">Card with pills</h5>
              <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
              <a class="btn btn-primary" href="#">Go somewhere</a>
            </div>
            <div role="tabpanel" id="tab-5" class="tab-pane fade text-center">
              <h5 class="card-title">Card with pills</h5>
              <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
              <a class="btn btn-primary" href="#">Go somewhere</a>
            </div>
            <div role="tabpanel" id="tab-6" class="tab-pane fade">
              <h5 class="card-title">Card with pills</h5>
              <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
              <a class="btn btn-primary" href="#">Go somewhere</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section> -->