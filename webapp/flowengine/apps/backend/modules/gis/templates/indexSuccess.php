<?php
/**
 * indexSuccess.php template.
 */
use_helper("I18N");
$agency_manager = new AgencyManager(); // OTB - Managing agency access
$otb_helper = new OTBHelper();
if ($sf_user->mfHasCredential('access_gis_unit')): ?>

    <div class="pageheader">
        <h2><i class="fa fa-home"></i><?php echo __('GIS'); ?></h2>
        <div class="breadcrumb-wrapper">
            <span class="label"><?php echo __('You are here'); ?>:</span>
            <ol class="breadcrumb">
                <li><a href="<?php echo public_path('/gis'); ?>backend.php"><?php echo __('Home'); ?></a></li>
                <li class="active"><?php echo __('GIS'); ?></li>
            </ol>
        </div>
    </div>

    <div class="contentpanel">
        <div class="panel panel-bordered radius-all">
            <div class="panel-body panel-body-nopadding">

                <!-- Top filter row -->
                <form id="filters" class="form-horizontal" style="margin-bottom: 20px;">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="subcounty">Subcounty</label>
                                <select class="form-control" id="subcounty" name="subcounty"></select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="ward">Ward</label>
                                <select class="form-control" id="ward" name="ward"></select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">Filter</button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Data table -->
                <!-- <table class="table table-striped table-bordered table-hover mb0" id="invoices" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?php echo __('Service'); ?></th>
                            <th><?php echo __('Application Id'); ?></th>
                            <th><?php echo __('Applicant'); ?></th>
                            <th><?php echo __('Current Stage'); ?></th>
                            <th><?php echo __('Invoice Paid.'); ?></th>
                            <th><?php echo __('Date registered'); ?></th>
                            <th><?php echo __('Owner'); ?></th>
                            <th><?php echo __('Approval Status'); ?></th>
                            <th><?php echo __('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody> -->
                <!-- Filled via jQuery/JS -->
                <!-- </tbody>
                </table> -->
                <table class="table table-bordered table-striped" id="applications-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Application No</th>
                            <th>Subcounty</th>
                            <th>Ward</th>
                            <th>Service Type</th>
                            <th>Stage</th>
                            <th>Status</th>
                            <th>Invoices Paid</th>
                            <th>Application Date</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <nav>
                    <ul class="pagination"></ul>
                </nav>

            </div>
        </div>
    </div>

    <script>
  const apiBase = '/backend.php/api';
  let currentPage = 1;

  function fetchApplications(page = 1) {
    const subcounty = $('#subcounty').val();
    const ward = $('#ward').val();
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();

    let url = `${apiBase}/applicationsList?page=${page}&limit=10`;

    if (subcounty) url += `&subcounty=${subcounty}`;
    if (ward) url += `&ward=${ward}`;
    if (startDate) url += `&start_date=${startDate}`;
    if (endDate) url += `&end_date=${endDate}`;

    $.get(url, function(response) {
      const tbody = $('#applications-table tbody');
      tbody.empty();

      (response.data || []).forEach((item, index) => {
        tbody.append(`
          <tr>
            <td>${index + 1}</td>
            <td>${item.application_number}</td>
            <td>${item.subcounty}</td>
            <td>${item.ward}</td>
            <td>${item.service_type}</td>
            <td>${item.current_stage}</td>
            <td>${item.approval_status}</td>
            <td>${item.invoices_paid ? 'Yes' : 'No'}</td>
            <td>${item.application_date}</td>
          </tr>
        `);
      });

      renderPagination(response.meta);
    });
  }

  function renderPagination(meta) {
    const pagination = $('.pagination');
    pagination.empty();

    if (!meta || meta.totalPages <= 1) return;

    for (let i = 1; i <= meta.totalPages; i++) {
      const active = i === meta.currentPage ? 'active' : '';
      pagination.append(`
        <li class="${active}"><a href="#" onclick="gotoPage(${i}); return false;">${i}</a></li>
      `);
    }
  }

  function gotoPage(page) {
    currentPage = page;
    fetchApplications(page);
  }

  function fetchSubcounties() {
    $.get(`${apiBase}/subCounties`, function(response) {
      const subcountySelect = $('#subcounty');
      subcountySelect.append('<option value="">-- Select Subcounty --</option>');
      response.forEach(sub => {
        subcountySelect.append(`<option value="${sub.name}">${sub.name}</option>`);
      });

      subcountySelect.change(function () {
        const selectedName = $(this).val();
        const selected = response.find(s => s.name === selectedName);
        const wards = selected ? selected.wards : [];
        const wardSelect = $('#ward');
        wardSelect.empty().append('<option value="">-- Select Ward --</option>');
        wards.forEach(w => {
          wardSelect.append(`<option value="${w.name}">${w.name}</option>`);
        });
      });
    });
  }

  $(document).ready(function() {
    fetchSubcounties();
    fetchApplications();

    $('#filters').submit(function(e) {
      e.preventDefault();
      fetchApplications(1);
    });
  });
</script>


<?php else: ?>
    <?php include_partial("settings/accessdenied"); ?>
<?php endif; ?>