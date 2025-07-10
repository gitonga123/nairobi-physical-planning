<?php

class SubcountyActions extends sfActions
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
            ->from('Subcounty s')
            ->orderBy('s.id ASC');
        $this->subcounties = $q->execute();

        $this->setLayout("layout-settings");
    }

    public function executeUpdatesubcounties(sfWebRequest $request)
    {
        $sub_counties = $this->sub_counties();

        var_dump($sub_counties);die;

        foreach ($sub_counties as $sub_county) {
            $q = Doctrine_Query::create()
                ->from('Subcounty s')
                ->where('s.name ?', $sub_county['title']);
            $found = $q->fetchOne();

            if ($found) {
                $found->setName($sub_county['title']);
                $found->setUuid($sub_county['id']);

                $found->save();
            } else {
                $record = new Subcounty();

                $record->setName($sub_county['title']);
                $record->setUuid($sub_county['id']);

                $record->save();
            }
        }

        return $this->renderText(json_encode(array('status' => 'success')));
    }


    public function sub_counties()
    {
        $url = sfConfig::get('app_api_jambo_url') . 'api/v1/county/sub_counties/';

        $stream = new Stream();

        error_log("Sub county list URL --->{$url}");
        error_log($_SESSION['jambo_token_backend']);

        $query_response = $stream->sendRequest([
            'url' => $url,
            'method' => 'GET',
            'ssl' => 'none',
            'contentType' => 'json',
            'headers' => [
                "Authorization" => "JWT {$_SESSION['jambo_token_backend']}",
            ]
        ]);

        var_dump($query_response);

        if ($query_response->status !== 201 || $query_response->status !== 200) {
            return [];
        }

        return $query_response->content->results;
    }
}