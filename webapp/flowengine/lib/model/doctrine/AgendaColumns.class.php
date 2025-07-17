<?php
class AgendaColumns extends BaseAgendaColumns
{
	public function getFormname()
	{
		$app_forms=Doctrine_Core::getTable('ApForms')->getAllApplicationForms(1);
		return $app_forms[$this->getFormId()];
	}
	public function getElements()
	{
		$form_elements=Doctrine_Core::getTable('ApFormElements')->getAllFormFieldsIncludeEmail($this->getFormId());
		$elements=json_decode($this->getElementId());
		$element_str='<ul>';
		foreach($elements as $e){
			$element_str.='<li>'.$form_elements[$e].'</li>';
		}
		$element_str.='</ul>';
		return $element_str;
	}
	public function getAppColums()
	{
		$colms=Doctrine_Core::getTable('FormEntry')->getColumnsAgenda();
		$selected=json_decode($this->getEntryColumn());
		$colm_str='<ul>';
		foreach($selected as $s){
			$colm_str.='<li>'.$colms[$s].'</li>';
		}
		$colm_str.='</ul>';
		return $colm_str;
	}
	public function getStagename()
	{
		$submenu=Doctrine_Core::getTable('SubMenus')->createQuery('s')->select('s.title')->where('s.id = ?',$this->getStage())->fetchArray();
		return $submenu[0]['title'];
	}
}
?>