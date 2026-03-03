<?php 

// Initiate the translator service
use_helper("I18N");

?>

  <!-- Restore last-child spacing in modals -->
  <style type="text/css">
.modal form .form-group:last-child {
  margin-bottom: 15px;
}
  </style>



<div class="contentpanel panel-email">
  <div class="panel panel-dark plot-details-panel">
    <div class="panel-heading">
      <h3 class="panel-title"><?php echo __('Plot Details'); ?></h3>

    </div>
	<div class="panel-heading">
		<button class="btn btn-success settings-margin42"><?php echo __('Refresh'); ?></button>
		<button class="btn btn-primary settings-margin42"><?php echo __('New Plot'); ?></button>
	</div>
    <div class="panel panel-body panel-body-nopadding">
        <table class="table dt-on-steroids mb0">
          <thead>
            <tr>
           
           
           
              <th><?php echo __('Owner Name'); ?></th>
              <th><?php echo __('Owner Phone'); ?></th>
              <th><?php echo __('Plot Number'); ?></th>
              <th><?php echo __('Block Number'); ?></th>
              <th><?php echo __('Ward'); ?></th>              
              <th><?php echo __('Plot Size (ha)'); ?></th>
              <th><?php echo __('Usage'); ?></th>
              <th><?php echo __('Amount Land Rates'); ?></th>
              <th><?php echo __('Actions'); ?></th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><?php echo __('Plot Detail') ?></h4>
          </div>
          <form method="POST" action="<?php echo public_path('plan/plot/save'); ?>" trash="<?php echo public_path('plan/plot/delete'); ?>">
            <div class="modal-body">
              <div class="modal-message"></div>
              <div class="alert hide"><p></p></div>
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label><?php echo __('Plot Number') ?></label>
                    <input type="text" class="form-control" name="plot[plot_no]" required />
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label><?php echo __('Owner Name') ?></label>
                    <select class="form-control" name="plot[owner_name]" required>
                      <option>L.R No</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label><?php echo __('Plot Size') ?></label>
                    <input type="text" class="form-control" name="plot[plot_size]" />
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label><?php echo __('owner_phone') ?></label>
                    <select class="form-control" name="plot[plot_status]" required>
                      <option value='Available'><?php echo __('Available'); ?></option>
                      <option value='Pending-Application'><?php echo __('Pending Application'); ?></option>
                      <option value='Contentious'><?php echo __('Contentious'); ?></option>
                      <option value='Black-Listed'><?php echo __('Black Listed'); ?></option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group">
                    <label><?php echo __('Longitude') ?></label>
                    <input type="text" class="form-control" name="plot[plot_long]" />
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label><?php echo __('Latitude') ?></label>
                    <input type="text" class="form-control" name="plot[plot_lat]" />
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-12">
                  <div class="form-group">
                    <label><?php echo __('Location') ?></label>
                    <input type="text" class="form-control" name="plot[plot_location]" />
                  </div>
                  <div class="form-group">
                    <label><?php echo __('Comments') ?></label>
                    <textarea class="form-control" name="plot[plot_comments]"></textarea>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close') ?></button>
              <button type="submit" class="btn btn-primary"><?php echo __('Save changes') ?></button>
              <input type="hidden" name="plot[id]" value="" class="form-control" />
              <input type="hidden" name="plot[_csrf_token]" value="<?php echo $csrfToken ?>" />
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
  <script src="<?php echo public_path('assets_unified/js/modules/plot/index.js'); ?>"></script>
