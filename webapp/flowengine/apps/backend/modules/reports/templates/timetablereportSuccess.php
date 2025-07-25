<?php
  $prefix_folder = dirname(__FILE__)."/../../../../../lib/vendor/form_builder/";
  require_once($prefix_folder.'includes/init.php');

  require_once($prefix_folder.'../../../config/form_builder_config.php');
  require_once($prefix_folder.'includes/db-core.php');
  require_once($prefix_folder.'includes/helper-functions.php');
  require_once($prefix_folder.'includes/check-session.php');

  require_once($prefix_folder.'includes/language.php');
  require_once($prefix_folder.'includes/entry-functions.php');
  require_once($prefix_folder.'includes/post-functions.php');
  require_once($prefix_folder.'includes/users-functions.php');

  $dbh = mf_connect_db();
  $mf_settings = mf_get_settings($dbh);

  $applications = null;
  $application_manager = new ApplicationManager();

  $q = Doctrine_Query::create()
      ->from('FormEntry a')
      ->where('a.form_id = ?', $form_id)
      ->andWhere('a.approved = ?', $stage_id)
      ->andWhere('a.approved <> 0');
  $applications = $q->execute();

  $q = Doctrine_Query::create()
     ->from("ApFormElements a")
     ->where("a.form_id = ?", $form_id)
     ->andWhere("a.element_status = ?", '1')
     ->andWhere("a.element_existing_form IS NULL")
     ->andWhere("a.element_type = ?", 'select');
  $filterelements = $q->execute();

  $sql_filter = "";
  foreach($filterelements as $element)
  {
     if($_GET['filter_'.$element->getElementId()])
     {
        $sql_filter .= " AND element_".$element->getElementId()." = ".$_GET['filter_'.$element->getElementId()];
     }
  }
?>

<div class="contentpanel">
      <div id="updatediv" name="updatediv"></div>

      <div class="col-md-12">
        <div class="panel panel-default panel-default panel-alt">
            <div class="panel-heading">
              <h4 class="panel-title">Filters:</h4>
            </div>
            <div class="panel-body">
              <?php

              foreach($filterelements as $element)
              {
                  ?>
                  <label><?php echo $element->getElementTitle(); ?>: </label>
                  <select id='filter_<?php echo $element->getElementId(); ?>' name='filter_<?php echo $element->getElementId(); ?>' onChange="window.location='<?php echo "http://".$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI]; ?>&filter_<?php echo $element->getElementId(); ?>=' + this.value;">
                      <option>Choosen an option</option>
                      <?php
                      $q = Doctrine_Query::create()
                         ->from("ApElementOptions a")
                         ->where("a.form_id = ?", $form_id)
                         ->andWhere("a.element_id = ?", $element->getElementId())
                         ->andWhere("a.live = ?", 1)
                         ->orderBy("a.option_text ASC");
                      $options = $q->execute();
                      foreach($options as $option)
                      {
                          $selected = "";
                          if($option->getOptionId() == $_GET['filter_'.$element->getElementId()])
                          {
                              $selected = "selected='selected'";
                          }

                          echo "<option value='".$option->getOptionId()."' ".$selected.">".$option->getOptionText()."</option>";
                      }
                      ?>
                  </select> &nbsp; &nbsp;
                  <?php
              }
              ?>
              &nbsp; &nbsp;
              <button type='button' onClick='window.location="/plan/reports/timetablereport?tr=1";'>Reset</button>
            </div>
        </div>
      </div>

      <div class="col-md-12">
        <div id="calendar"></div>
      </div><!-- col-md-9 -->


      <div class="col-md-12">
        <div class="panel panel-default panel-default panel-alt">
          <div class="panel-heading">
            <h4 class="panel-title">Applications</h4>
          </div>
          <div class="panel-body">
            <div id='external-events'>
              <?php
                $app_count = 0;
                foreach($applications as $application)
                {
                  //check if date field is already set, if it is then skip
                  $date_set  = false;

                  $query = "SELECT * FROM ap_form_".$application->getFormId()." WHERE id = ".$application->getEntryId();
                  $sth = mf_do_query($query,array(),$dbh);

                  if($sth === false) {
                     continue;
                  } else {
                     $application_row = mf_do_fetch_result($sth);
                     if($application_row['element_'.$element_id])
                     {
                       $app_count++;
                       $date_set = true;
                     }
                  }

                  if(!$date_set)
                  {
                  ?>
                  <div class='external-event'><?php echo $application->getApplicationId(); ?></div>
                  <?php
                  }
                }
              ?>
            </div>
            <h2>Total: <?php echo $app_count; ?></h2>
          </div>
        </div>
      </div><!-- col-md-3 -->

  </div>

<script>

  jQuery(document).ready(function() {


		/* initialize the external events
		-----------------------------------------------------------------*/

		jQuery('#external-events div.external-event').each(function() {

			// create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
			// it doesn't need to have a start or end
			var eventObject = {
				title: $.trim($(this).text()) // use the element's text as the event title
			};

			// store the Event Object in the DOM element so we can get to it later
			jQuery(this).data('eventObject', eventObject);

			// make the event draggable using jQuery UI
			jQuery(this).draggable({
				zIndex: 999,
				revert: true,      // will cause the event to go back to its
				revertDuration: 0  //  original position after the drag
			});

		});


		/* initialize the calendar
		-----------------------------------------------------------------*/

		jQuery('#calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
      events: [
      <?php
      $date_array = array();
      $count_array = array();

      foreach($applications as $application)
      {
        $sql = "SELECT * FROM ap_form_".$application->getFormId()." WHERE id = ".$application->getEntryId().$sql_filter;
        $sth = mf_do_query($sql,array(),$dbh);

        if($sth === false) {
           continue;
        } else {
           $application_row = mf_do_fetch_result($sth);
           if($application_row['element_'.$element_id])
           {
              if(!in_array($application_row['element_'.$element_id], $date_array)){
                $date_array[]=$application_row['element_'.$element_id];
                $count_array[$application_row['element_'.$element_id]] = 1;
              }
              else
              {
                $count = $count_array[$application_row['element_'.$element_id]];
                $count++;
                $count_array[$application_row['element_'.$element_id]] = $count;
              }
             ?>
             {
               title: '<?php echo $application->getApplicationId(); ?>',
               start: '<?php echo $application_row['element_'.$element_id]; ?>T<?php echo $application_row['element_'.$time_element_id]; ?>'
             },
           <?php
           }
        }
      }

      foreach($date_array as $key=>$value){
          ?>
             {
               title: 'Total (<?php echo $count_array[$value]; ?>)',
               start: '<?php echo $value; ?>T00:00:00'
             },
           <?php
      }
      ?>
			],
			editable: true,
      allDayDefault: false,
			droppable: true, // this allows things to be dropped onto the calendar !!!
			drop: function(date, allDay) { // this function is called when something is dropped
				// retrieve the dropped element's stored Event Object
				var originalEventObject = jQuery(this).data('eventObject');

				// we need to copy it, so that multiple events don't have a reference to the same object
				var copiedEventObject = $.extend({}, originalEventObject);

				// assign it the date that was reported
				copiedEventObject.start = date;
				copiedEventObject.allDay = allDay;

        dateObj = new Date(copiedEventObject.start);
        dateIntNTZ = dateObj.getTime() - dateObj.getTimezoneOffset() * 60 * 1000;
        dateObjNTZ = new Date(dateIntNTZ);

        var date_ev = dateObjNTZ.toISOString().slice(0, 10);
        var time_ev = dateObjNTZ.toISOString().slice(11, 19);

        $.ajax({url:"/plan/reports/setdate?applicationid=" + copiedEventObject.title + "&elementid=<?php echo $element_id; ?>&timeelementid=<?php echo $time_element_id; ?>&date=" + date_ev + "&time=" + time_ev,success:function(result){
          //$("#updatediv").html(result);
        }});

				// render the event on the calendar
				// the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
				jQuery('#calendar').fullCalendar('renderEvent', copiedEventObject, true);


				// if so, remove the element from the "Draggable Events" list
				jQuery(this).remove();

			},
      eventDrop: function(event, element) {

        dateObj = new Date(event.start);
        dateIntNTZ = dateObj.getTime() - dateObj.getTimezoneOffset() * 60 * 1000;
        dateObjNTZ = new Date(dateIntNTZ);

        var date_ev = dateObjNTZ.toISOString().slice(0, 10);
        var time_ev = dateObjNTZ.toISOString().slice(11, 19);

        $.ajax({url:"/plan/reports/setdate?applicationid=" + event.title + "&elementid=<?php echo $element_id; ?>&timeelementid=<?php echo $time_element_id; ?>&date=" + date_ev + "&time=" + time_ev,success:function(result){
          //$("#updatediv").html(result);
        }});

        $('#calendar').fullCalendar('updateEvent', event);

    },
    eventClick: function(event, element) {

      var formentryid = "";

      $.ajax({url:"/plan/reports/getformentryid?applicationid=" + event.title,success:function(result){
        formentryid = result;
        if(formentryid != 0)
        {
          window.location = "/plan/applications/view/id/" + formentryid;
        }
      }});

      $('#calendar').fullCalendar('updateEvent', event);

    }
		});


	});

</script>
