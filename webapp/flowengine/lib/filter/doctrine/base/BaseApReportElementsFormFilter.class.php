<?php

/**
 * ApReportElements filter form base class.
 *
 * @package    permitflow
 * @subpackage filter
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApReportElementsFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'access_key'               => new sfWidgetFormFilterInput(),
      'chart_position'           => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_status'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_datasource'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_type'               => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_enable_filter'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_filter_type'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_title'              => new sfWidgetFormFilterInput(),
      'chart_title_position'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_title_align'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_width'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_height'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_background'         => new sfWidgetFormFilterInput(),
      'chart_theme'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_legend_visible'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_legend_position'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_labels_visible'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_labels_position'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_labels_template'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_labels_align'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_tooltip_visible'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_tooltip_template'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_gridlines_visible'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_bar_color'          => new sfWidgetFormFilterInput(),
      'chart_is_stacked'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_is_vertical'        => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_line_style'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_axis_is_date'       => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_date_range'         => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_date_period_value'  => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_date_period_unit'   => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_date_axis_baseunit' => new sfWidgetFormFilterInput(),
      'chart_date_range_start'   => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'chart_date_range_end'     => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate())),
      'chart_grid_page_size'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'chart_grid_max_length'    => new sfWidgetFormFilterInput(array('with_empty' => false)),
    ));

    $this->setValidators(array(
      'access_key'               => new sfValidatorPass(array('required' => false)),
      'chart_position'           => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_status'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_datasource'         => new sfValidatorPass(array('required' => false)),
      'chart_type'               => new sfValidatorPass(array('required' => false)),
      'chart_enable_filter'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_filter_type'        => new sfValidatorPass(array('required' => false)),
      'chart_title'              => new sfValidatorPass(array('required' => false)),
      'chart_title_position'     => new sfValidatorPass(array('required' => false)),
      'chart_title_align'        => new sfValidatorPass(array('required' => false)),
      'chart_width'              => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_height'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_background'         => new sfValidatorPass(array('required' => false)),
      'chart_theme'              => new sfValidatorPass(array('required' => false)),
      'chart_legend_visible'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_legend_position'    => new sfValidatorPass(array('required' => false)),
      'chart_labels_visible'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_labels_position'    => new sfValidatorPass(array('required' => false)),
      'chart_labels_template'    => new sfValidatorPass(array('required' => false)),
      'chart_labels_align'       => new sfValidatorPass(array('required' => false)),
      'chart_tooltip_visible'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_tooltip_template'   => new sfValidatorPass(array('required' => false)),
      'chart_gridlines_visible'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_bar_color'          => new sfValidatorPass(array('required' => false)),
      'chart_is_stacked'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_is_vertical'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_line_style'         => new sfValidatorPass(array('required' => false)),
      'chart_axis_is_date'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_date_range'         => new sfValidatorPass(array('required' => false)),
      'chart_date_period_value'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_date_period_unit'   => new sfValidatorPass(array('required' => false)),
      'chart_date_axis_baseunit' => new sfValidatorPass(array('required' => false)),
      'chart_date_range_start'   => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'chart_date_range_end'     => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDateTime(array('required' => false)))),
      'chart_grid_page_size'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chart_grid_max_length'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('ap_report_elements_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApReportElements';
  }

  public function getFields()
  {
    return array(
      'access_key'               => 'Text',
      'form_id'                  => 'Number',
      'chart_id'                 => 'Number',
      'chart_position'           => 'Number',
      'chart_status'             => 'Number',
      'chart_datasource'         => 'Text',
      'chart_type'               => 'Text',
      'chart_enable_filter'      => 'Number',
      'chart_filter_type'        => 'Text',
      'chart_title'              => 'Text',
      'chart_title_position'     => 'Text',
      'chart_title_align'        => 'Text',
      'chart_width'              => 'Number',
      'chart_height'             => 'Number',
      'chart_background'         => 'Text',
      'chart_theme'              => 'Text',
      'chart_legend_visible'     => 'Number',
      'chart_legend_position'    => 'Text',
      'chart_labels_visible'     => 'Number',
      'chart_labels_position'    => 'Text',
      'chart_labels_template'    => 'Text',
      'chart_labels_align'       => 'Text',
      'chart_tooltip_visible'    => 'Number',
      'chart_tooltip_template'   => 'Text',
      'chart_gridlines_visible'  => 'Number',
      'chart_bar_color'          => 'Text',
      'chart_is_stacked'         => 'Number',
      'chart_is_vertical'        => 'Number',
      'chart_line_style'         => 'Text',
      'chart_axis_is_date'       => 'Number',
      'chart_date_range'         => 'Text',
      'chart_date_period_value'  => 'Number',
      'chart_date_period_unit'   => 'Text',
      'chart_date_axis_baseunit' => 'Text',
      'chart_date_range_start'   => 'Date',
      'chart_date_range_end'     => 'Date',
      'chart_grid_page_size'     => 'Number',
      'chart_grid_max_length'    => 'Number',
    );
  }
}
