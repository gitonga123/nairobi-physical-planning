<?php
/**
 * Languages actions.
 *
 * Language Management Service
 *
 * @package    backend
 * @subpackage languages
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */

class languagesActions extends sfActions
{
      /**
	 * Executes 'index' function
	 *
	 * Display a list of existing objects
	 *
	 * @param sfRequest $request A request object
	 */
      public function executeIndex(sfWebRequest $request)
      {
            //Get list of all objects
            $q = Doctrine_Query::create()
                  ->from('ExtLocales a')
                  ->orderBy('a.local_title ASC');
            $this->languages = $q->execute();

            $this->setLayout("layout-settings");
      }

      /**
	 * Executes 'new' function
	 *
	 * Create a new object
	 *
	 * @param sfRequest $request A request object
	 */
      public function executeNew(sfWebRequest $request)
      {
            $this->form = new ExtLocalesForm();

            $this->setLayout("layout-settings");
      }

      /**
	 * Executes 'create' function
	 *
	 * Save a new object
	 *
	 * @param sfRequest $request A request object
	 */
      public function executeCreate(sfWebRequest $request)
      {
            $this->forward404Unless($request->isMethod(sfRequest::POST));

            $this->form = new ExtLocalesForm();

            $this->processForm($request, $this->form);

            $this->setTemplate('new');
      }

      /**
	 * Executes 'edit' function
	 *
	 * Edit an existing object
	 *
	 * @param sfRequest $request A request object
	 */
      public function executeEdit(sfWebRequest $request)
      {
            $this->forward404Unless($language = Doctrine_Core::getTable('ExtLocales')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));
            
            $this->form = new ExtLocalesForm($language);

            $this->setLayout("layout-settings");
      }

      /**
	 * Executes 'update' action
	 *
	 * Update an existing object
	 *
	 * @param sfRequest $request A request object
	 */
      public function executeUpdate(sfWebRequest $request)
      {
            $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
            $this->forward404Unless($language = Doctrine_Core::getTable('ExtLocales')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

            $this->form = new ExtLocalesForm($language);

            $this->processForm($request, $this->form);

            $this->setTemplate('edit');
      }

      /**
	 * Executes 'processForm' function
	 *
	 * Validate the form and save the object
	 *
	 * @param sfRequest $request A request object
	 */
      protected function processForm(sfWebRequest $request, sfForm $form)
      {
            $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
            if ($form->isValid())
            {
                  $language = $form->save();

                  $this->redirect('/plan/languages/index');
            }
      }

      /**
	 * Executes 'delete' action
	 *
	 * Delete the object
	 *
	 * @param sfRequest $request A request object
	 */
      public function executeDelete(sfWebRequest $request)
      {
            $this->forward404Unless($language = Doctrine_Core::getTable('ExtLocales')->find(array($request->getParameter('id'))), sprintf('Object content does not exist (%s).', $request->getParameter('id')));

            $language->delete();

            $this->redirect('/plan/languages/index');
      }
  
      /**
      * Executes 'Translate' action
      *
      * Shows a form to allow translation of labels to different language
      *
      * @param sfRequest $request A request object
      */
      public function executeTranslate(sfWebRequest $request)
      {
            $this->filter = $request->getParameter("filter");
            $this->setLayout("layout-settings");
      }

    /**
     * Executes 'Savetranslate' action
     *
     * Shows a form to allow translation of labels to different language
     *
     * @param sfRequest $request A request object
     */
    public function executeSavetranslate(sfWebRequest $request)
    {
      $filter = $request->getPostParameter("filter");

      $q = Doctrine_Query::create()
         ->from("ExtLocales a")
         ->orderBy("a.local_title ASC");
      $languages = $q->execute();

      foreach($languages as $language)
      {
        $filename = "messages.".$language->getLocaleIdentifier().".xml";

$translation = '<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE xliff PUBLIC "-//XLIFF//DTD XLIFF//EN" "http://www.oasis-open.org/committees/xliff/documents/xliff.dtd" >
<xliff version="1.0">
  <file original="global" source-language="'.$language->getLocaleIdentifier().'" datatype="plaintext">
    <body>';

$count = 0;

$targets = $request->getPostParameter("locale_".$language->getLocaleIdentifier());

foreach($request->getPostParameter("key_locales") as $key)
{
  $count_ahead = $count+1;
$translation .= '
  <trans-unit id="'.$count_ahead.'">
    <source>'.$key.'</source>
    <target>'.$targets[$count].'</target>
  </trans-unit>';

  $count++;
}

$translation .= '
    </body>
  </file>
</xliff>';

        $prefix_folder = dirname(__FILE__)."/../../../../../apps/".$filter."/i18n/";

        $translation_file = fopen($prefix_folder.$filename, "w") or die("Unable to ".$prefix_folder.$filename);
        fwrite($translation_file, $translation);
        fclose($translation_file);
      }

      $this->redirect("/plan/languages/index");
    }

    /**
     * Executes 'Setlocale' action
     *
     * Change the display language for the currently logged in reviewer
     *
     * @param sfRequest $request A request object
     */
    public function executeSetlocale(sfWebRequest $request)
    {
      $this->getUser()->setCulture($request->getParameter("code"));
      if($request->getReferer())
      {
            $this->redirect($request->getReferer());
      }
      else 
      {
            $this->redirect("/plan");
      }
    }

}
