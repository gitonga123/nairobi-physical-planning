<?php
/**
 * indexSuccess.php template.
 */
use_helper("I18N");
$agency_manager = new AgencyManager(); // OTB - Managing agency access
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

        <form id="filters" class="form-horizontal" style="margin-bottom: 20px;" width="100%">

          <select size="1" name="application_type" id="application_type" aria-controls="table2"
            class="select2 form-control" width="100%" style="margin-bottom: 20px;">
            <option value="0">Select Service</option>
            <?php
            foreach ($application_types as $option => $value) {
              echo "<option value='{$option}'>{$value}</option>";
            }
            ?>
          </select>


          <table class="table table-striped table-hover mb0 border-top-0 border-left-0 border-right-0 panel-table"
            width="100%">

            <tbody>

              <tr>
                <th class="border-bottom-1">
                  <label for="subcounty">Sub County</label>
                  <select class="form-control" id="subcounty" name="subcounty"></select>
                </th>
                <th class="border-bottom-1">
                  <label for="ward">Ward</label>
                  <select class="form-control" id="ward" name="ward"></select>
                </th>
                <th class="border-bottom-1">
                  <label for="start_date">Starting From</label>
                  <input type="date" class="form-control" id="start_date" name="start_date">
                </th>
                <th class="border-bottom-1">
                  <label for="end_date">Ending</label>
                  <input type="date" class="form-control" id="end_date" name="end_date">
                </th>
                <th class="border-bottom-1">
                  <label>&nbsp;</label>
                  <button type="submit" class="btn btn-primary btn-block">Filter</button>
                  </td>
              </tr>
            </tbody>
          </table>
        </form>

        <table class="table table-striped table-bordered table-hover mb0" id="applications-table" width="100%">
          <thead>
            <tr>
              <th>#</th>
              <th><?php echo __('Service'); ?></th>
              <th><?php echo __('Application'); ?></th>
              <th><?php echo __('Current Stage'); ?></th>
              <th><?php echo __('Applicant'); ?></th>
              <th><?php echo __('Plot Details.'); ?></th>
              <th><?php echo __('Plot Location.'); ?></th>
              <th><?php echo __('Plot Coordinates'); ?></th>
              <th><?php echo __('Approval Status'); ?></th>
              <th><?php echo __('Action'); ?></th>
            </tr>
          </thead>
          <tbody>
            <tr class="application-row" id="loading-row">
              <td colspan="10" class="text-center application-row" style="height: 100px;">
                <i class="fa fa-spinner fa-spin"></i> Loading applications...
              </td>
            </tr>
          </tbody>
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
      const permitType = $('#application_type').val(); // <-- New line

      let url = `${apiBase}/applicationsList?page=${page}&limit=10`;

      if (subcounty) url += `&subcounty=${subcounty}`;
      if (ward) url += `&ward=${ward}`;
      if (startDate) url += `&start_date=${startDate}`;
      if (endDate) url += `&end_date=${endDate}`;
      if (permitType && permitType !== '0') url += `&application_type=${permitType}`; // <-- New line

      const tbody = $('#applications-table tbody');
      tbody.html(`
    <tr id="loading-row">
      <td colspan="10" class="text-center">
        <i class="fa fa-spinner fa-spin"></i> Loading applications...
      </td>
    </tr>
  `);

      $.get(url, function (data) {
        tbody.empty();

        let response = JSON.parse(data);
        const items = response.data || [];

        if (items.length === 0) {
          tbody.append(`
        <tr>
          <td colspan="10" class="text-center text-muted">No applications found.</td>
        </tr>
      `);
        } else {
          items.forEach((item, index) => {
            tbody.append(`
          <tr class="application-row">
            <td>${index + 1}</td>
            <td><strong>${item.service_type}</strong></td>
            <td><a href="/backend.php/applications/view/id/${item.application_id}" target="_blank">${item.application_number}</a></td>
            <td><a href="/backend.php/dashboard/index/current/available/filter/${item.current_stage_id}" target="_blank">${item.current_stage}</a></td>
            <td>${item.owner ?? "-"}</td>
            <td>
              <small>
                <strong>Block:</strong> ${item.block_number}<br>
                <strong>Plot:</strong> ${item.plot_no}
              </small>
            </td>
            <td>
              <small>
                <strong>Subcounty:</strong> ${item.subcounty}<br>
                <strong>Ward:</strong> ${item.ward}
              </small>
            </td>
            <td>
              <small>
                <strong>Lat:</strong> ${item?.latitude ?? "-"}<br>
                <strong>Long:</strong> ${item?.longitude ?? "-"}
              </small>
            </td>
            <td>${item.approval_status}</td>
            <td><small>${item.application_date}</small></td>
          </tr>

        `);
          });
        }

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
      $.get(`${apiBase}/subCounties`, function (data) {
        const subcountySelect = $('#subcounty');
        subcountySelect.append('<option value="">-- Select Subcounty --</option>');


        let response_data = JSON.parse(data);
        console.log(response_data);

        let response = response_data.data;

        console.log(response);
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

    $(document).ready(function () {
      fetchSubcounties();
      fetchApplications();

      $('#filters').submit(function (e) {
        e.preventDefault();
        fetchApplications(1);
      });

      $('#application_type').change(function () {
        fetchApplications(1);
      });

    });
  </script>
  <style>
    .application-row {
      height: 60px;
      vertical-align: middle;
    }
  </style>


<?php else: ?>
  <?php include_partial("settings/accessdenied"); ?>
<?php endif; ?>