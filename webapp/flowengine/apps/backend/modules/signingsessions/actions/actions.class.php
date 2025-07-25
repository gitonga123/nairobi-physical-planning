<?php

/**
 * signingsessions actions.
 *
 * @package    permitflow
 * @subpackage signingsessions
 * @author     James Gathu
 * @version    SVN: $Id$
 */
class signingsessionsActions extends sfActions
{

    public function executeIndex(sfWebRequest $request)
    {
        $q = "SELECT uss.*, CONCAT(cu.strfirstname, ' ',cu.strlastname) name FROM user_signings uss LEFT JOIN cf_user cu ON cu.nid = uss.user_id";
        $this->configs = Doctrine_Manager::getInstance()->getCurrentConnection()->fetchAssoc($q);
        $this->setLayout("layout-settings");
    }

    public function executeCreate(sfWebRequest $request)
    {
        $this->setLayout("layout-settings");

        $this->users = Doctrine_Query::create()
            ->from('CfUser')
            ->execute();

        $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
        if ($request->getParameter('id')) {
            $this->config = $conn->fetchAssoc("SELECT * FROM user_signings WHERE  id = " . $request->getParameter('id'))[0];
        }

        if ($request->getMethod() == "POST") {
            $user_id = $request->getPostParameter('user_id');
            $total_allowed_pa = $request->getPostParameter('total_allowed_pa');

            # confirm if it exists
            $q = "SELECT * FROM user_signings WHERE user_id = $user_id";
            if ($result = $conn->fetchAssoc($q)) {
                $q = "UPDATE user_signings SET total_allowed_pa = $total_allowed_pa WHERE user_id = $user_id";
            } else {
                $q = "INSERT INTO user_signings(user_id, total_allowed_pa) VALUES ($user_id, $total_allowed_pa)";
            }
            $conn->execute($q);

            $this->redirect('/plan/signingsessions/index');
        }
    }

    /**
     * @param sfWebRequest $request
     * @throws Doctrine_Connection_Exception
     */
    public function executeAdd(sfWebRequest $request)
    {
        $current_session = Functions::lastSigningSession();
        $current_session_id = $current_session['id'];

        $docs = json_decode($current_session['documents']);

        if ($ids = $request->getParameter('ids')) {
            $type = $request->getParameter('type');

            foreach ($ids as $id) {
                if ($type == 'SavedPermit') {
                    $permit = Doctrine_Core::getTable($type)->find($id);

                    $document = [
                        'id' => $permit->getId(),
                        'url' => $permit->getUnSignedFilePath(),
                        'type' => $type,
                        'name' => $permit->getTemplate()->getTitle(),
                        'slug'
                    ];

                    if (!Functions::isDocumentInSigningSession($document['url']))
                        array_push($docs, $document);
                }
            }
        } else {
            $url = $request->getParameter('document');
            if (!Functions::isDocumentInSigningSession($url))
                array_push($docs, [
                    'id' => $request->getParameter('id'),
                    'name' => $request->getParameter('name'),
                    'url' => $url,
                    'type' => $request->getParameter('type'),
                    'slug' => $request->getParameter('slug'),
                    'application_id' => $request->getParameter('application_id'),
                ]);
        }

        if ($docs = json_encode($docs)) {
            Doctrine_Manager::getInstance()->getCurrentConnection()->execute("UPDATE user_signing_sessions SET documents = '$docs' WHERE id = $current_session_id ");
        }

        if ($redirect_to = $request->getParameter('redirect_to')) {
            $this->redirect($redirect_to);
        }
    }

    /**
     * @param sfWebRequest $request
     * @throws Doctrine_Connection_Exception
     */
    public function executeRemove(sfWebRequest $request)
    {
        $document = Functions::lastSigningSession();
        $url = $request->getParameter('document');

        $docs = json_decode($document['documents']);
        $docs = array_filter($docs,
            function ($doc) use ($url) {
                return $doc->url != $url;
            });
        $docs = json_encode(array_values($docs));
        $id = $document['id'];

        Doctrine_Manager::getInstance()->getCurrentConnection()->execute("UPDATE user_signing_sessions SET documents = '$docs' WHERE id = $id");

        if ($redirect_to = $request->getParameter('redirect_to')) {
            $this->redirect($redirect_to);
        }
    }

    public function executeSign(sfWebRequest $request)
    {
        # get my last signing session
        # get the involved documents
        # proceed to sign
        $last_session = Functions::lastSigningSession();

        $ids = 'id[]=' . implode('&id[]=', array_map(function ($document) {
                return $document->id;
            }, json_decode($last_session['documents'])));

        $url = "/plan/permits/signing?$ids&permitaction=signdocument&l_redirect=/plan/dashboard";
        $this->redirect($url);
    }
}
