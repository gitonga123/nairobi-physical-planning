<?php

class LogViewerActions extends sfActions
{
    private $cache;
    private $path_key = 'system_log_file_path_new';
    private $perPage = 50;

    public function initialize($context, $moduleName, $actionName)
    {
        parent::initialize($context, $moduleName, $actionName);

        $this->cache = new sfFileCache([
            'cache_dir' => sfConfig::get('sf_cache_dir') . '/data',
        ]);


    }

    public function executeIndex(sfWebRequest $request)
    {
        // Get all log paths for sidebar display
        $logPaths = Doctrine_Query::create()
            ->from('SystemLogPath s')
            ->orderBy('s.id ASC')
            ->fetchArray();

        $fileId = $request->getParameter('id');
        $selectedPath = null;

        if ($fileId) {
            $selectedPath = Doctrine_Query::create()
                ->from('SystemLogPath s')
                ->where('s.id = ?', $fileId)
                ->fetchOne(array(), Doctrine_Core::HYDRATE_ARRAY);
        }

        $severity = $request->getParameter('severity', '');
        $page = max((int) $request->getParameter('page', 1), 1);

        // Default to empty log lines
        $lines = [];

        if ($selectedPath && isset($selectedPath['path'])) {
            $filePath = $selectedPath['path'];

            if (file_exists($filePath)) {
                $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $lines = array_reverse($lines);

                if (!empty($severity)) {
                    $lines = array_filter($lines, function ($line) use ($severity) {
                        return stripos($line, $severity) !== false;
                    });
                }
            }
        }

        $total = count($lines);
        $offset = ($page - 1) * $this->perPage;
        $lines = array_slice($lines, $offset, $this->perPage);

        $this->lines = $lines;
        $this->logPaths = $logPaths;
        $this->currentSeverity = $severity;
        $this->fileKey = $fileId;
        $this->page = $page;
        $this->totalPages = ceil($total / $this->perPage);

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
        $this->form = new SystemLogPathForm();

        $this->setLayout("layout-settings");
    }

    public function executeCreate(sfWebRequest $request)
    {
        Audit::audit("", "Created System Log Path");

        $this->forward404Unless($request->isMethod(sfRequest::POST));

        $this->form = new SystemLogPathForm();
        $this->new = true;

        $this->processForm($request, $this->form);
        $this->setTemplate('new');
    }

    public function executeEdit(sfWebRequest $request)
    {
        $this->forward404Unless($item = Doctrine_Core::getTable('SystemLogPath')->find($request->getParameter('id')));

        $this->form = new SystemLogPathForm($item);
        $this->filter = $request->getParameter("filter");
        $this->setLayout("layout-settings");
    }

    public function executeUpdate(sfWebRequest $request)
    {
        Audit::audit("", "Updated existing system path");

        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
        $this->forward404Unless($item = Doctrine_Core::getTable('SystemLogPath')->find($request->getParameter('id')));

        $this->form = new SystemLogPathForm($item);
        $this->processForm($request, $this->form);
        $this->setTemplate('edit');
    }

    public function executeDelete(sfWebRequest $request)
    {
        Audit::audit("", "Deleted system log path");

        $this->forward404Unless($item = Doctrine_Core::getTable('SystemLogPath')->find($request->getParameter('id')));

        $item->setDeleted("1");
        $item->save();

        $this->cache->remove($this->path_key);

        $this->redirect('/backend.php/logviewer/index');
    }

    protected function processForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));

        if ($form->isValid()) {
            $form->save();
            $this->cache->remove($this->path_key);
            $this->redirect('/backend.php/logviewer/index');
        }
    }
}
