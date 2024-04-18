<?php
class WorkflowCategoryForm extends BaseWorkflowCategoryForm
{
  public function configure()
  {
	  $this->widgetSchema['order_id']= new sfWidgetFormInputHidden();
	  $this->setDefault('order_id',0);
  }
}
