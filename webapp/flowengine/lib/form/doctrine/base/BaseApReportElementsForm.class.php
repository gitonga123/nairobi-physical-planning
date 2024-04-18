<?php

/**
 * ApReportElements form base class.
 *
 * @method ApReportElements getObject() Returns the current form's model object
 *
 * @package    permitflow
 * @subpackage form
 * @author     Webmasters Africa
 * @version    SVN: $Id$
 */
abstract class BaseApReportElementsForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'access_key'               => new sfWidgetFormInputText(),
      'form_id'                  => new sfWidgetFormInputHidden(),
      'chart_id'                 => new sfWidgetFormInputHidden(),
      'chart_position'           => new sfWidgetFormInputText(),
      'chart_status'             => new sfWidgetFormInputText(),
      'chart_datasource'         => new sfWidgetFormInputText(),
      'chart_type'               => new sfWidgetFormInputText(),
      'chart_enable_filter'      => new sfWidgetFormInputText(),
      'chart_filter_type'        => new sfWidgetFormInputText(),
      'chart_title'              => new sfWidgetFormTextarea(),
      'chart_title_position'     => new sfWidgetFormInputText(),
      'chart_title_align'        => new sfWidgetFormInputText(),
      'chart_width'              => new sfWidgetFormInputText(),
      'chart_height'             => new sfWidgetFormInputText(),
      'chart_background'         => new sfWidgetFormInputText(),
      'chart_theme'              => new sfWidgetFormInputText(),
      'chart_legend_visible'     => new sfWidgetFormInputText(),
      'chart_legend_position'    => new sfWidgetFormInputText(),
      'chart_labels_visible'     => new sfWidgetFormInputText(),
      'chart_labels_position'    => new sfWidgetFormInputText(),
      'chart_labels_template'    => new sfWidgetFormInputText(),
      'chart_labels_align'       => new sfWidgetFormInputText(),
      'chart_tooltip_visible'    => new sfWidgetFormInputText(),
      'chart_tooltip_template'   => new sfWidgetFormInputText(),
      'chart_gridlines_visible'  => new sfWidgetFormInputText(),
      'chart_bar_color'          => new sfWidgetFormInputText(),
      'chart_is_stacked'         => new sfWidgetFormInputText(),
      'chart_is_vertical'        => new sfWidgetFormInputText(),
      'chart_line_style'         => new sfWidgetFormInputText(),
      'chart_axis_is_date'       => new sfWidgetFormInputText(),
      'chart_date_range'         => new sfWidgetFormInputText(),
      'chart_date_period_value'  => new sfWidgetFormInputText(),
      'chart_date_period_unit'   => new sfWidgetFormInputText(),
      'chart_date_axis_baseunit' => new sfWidgetFormInputText(),
      'chart_date_range_start'   => new sfWidgetFormDate(),
      'chart_date_range_end'     => new sfWidgetFormDate(),
      'chart_grid_page_size'     => new sfWidgetFormInputText(),
      'chart_grid_max_length'    => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'access_key'               => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'form_id'                  => new sfValidatorChoice(array('choices' => array($this->getObject()->get('form_id')), 'empty_value' => $this->getObject()->get('form_id'), 'required' => false)),
      'chart_id'                 => new sfValidatorChoice(array('choices' => array($this->getObject()->get('chart_id')), 'empty_value' => $this->getObject()->get('chart_id'), 'required' => false)),
      'chart_position'           => new sfValidatorInteger(array('required' => false)),
      'chart_status'             => new sfValidatorInteger(array('required' => false)),
      'chart_datasource'         => new sfValidatorString(array('max_length' => 25, 'required' => false)),
      'chart_type'               => new sfValidatorString(array('max_length' => 25, 'required' => false)),
      'chart_enable_filter'      => new sfValidatorInteger(array('required' => false)),
      'chart_filter_type'        => new sfValidatorString(array('max_length' => 5, 'required' => false)),
      'chart_title'              => new sfValidatorString(array('required' => false)),
      'chart_title_position'     => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'chart_title_align'        => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'chart_width'              => new sfValidatorInteger(array('required' => false)),
      'chart_height'             => new sfValidatorInteger(array('required' => false)),
      'chart_background'         => new sfValidatorString(array('max_length' => 8, 'required' => false)),
      'chart_theme'              => new sfValidatorString(array('max_length' => 25, 'required' => false)),
      'chart_legend_visible'     => new sfValidatorInteger(array('required' => false)),
      'chart_legend_position'    => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'chart_labels_visible'     => new sfValidatorInteger(array('required' => false)),
      'chart_labels_position'    => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'chart_labels_template'    => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'chart_labels_align'       => new sfValidatorString(array('max_length' => 10, 'required' => false)),
      'chart_tooltip_visible'    => new sfValidatorInteger(array('required' => false)),
      'chart_tooltip_template'   => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'chart_gridlines_visible'  => new sfValidatorInteger(array('required' => false)),
      'chart_bar_color'          => new sfValidatorString(array('max_length' => 8, 'required' => false)),
      'chart_is_stacked'         => new sfValidatorInteger(array('required' => false)),
      'chart_is_vertical'        => new sfValidatorInteger(array('required' => false)),
      'chart_line_style'         => new sfValidatorString(array('max_length' => 6, 'required' => false)),
      'chart_axis_is_date'       => new sfValidatorInteger(array('required' => false)),
      'chart_date_range'         => new sfValidatorString(array('max_length' => 6, 'required' => false)),
      'chart_date_period_value'  => new sfValidatorInteger(array('required' => false)),
      'chart_date_period_unit'   => new sfValidatorString(array('max_length' => 5, 'required' => false)),
      'chart_date_axis_baseunit' => new sfValidatorString(array('max_length' => 5, 'required' => false)),
      'chart_date_range_start'   => new sfValidatorDate(array('required' => false)),
      'chart_date_range_end'     => new sfValidatorDate(array('required' => false)),
      'chart_grid_page_size'     => new sfValidatorInteger(array('required' => false)),
      'chart_grid_max_length'    => new sfValidatorInteger(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('ap_report_elements[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ApReportElements';
  }

}
