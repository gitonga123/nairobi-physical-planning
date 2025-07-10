<?php

class WardActions extends sfActions
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
            ->from('Ward w')
            ->orderBy('w.id ASC');
        $this->wards = $q->execute();

        $this->setLayout("layout-settings");
    }

    public function executeUpdatewards(sfWebRequest $request)
    {
        $wards = $this->wards();


        foreach ($wards as $ward) {
            $q = Doctrine_Query::create()
                ->from('Ward w')
                ->where('w.name ?', $ward['title']);
            $found = $q->fetchOne();

            $q = Doctrine_Query::create()
                ->from('Subcounty s')
                ->where('s.name ?', $ward['sub_county']['title']);
            $sub_county = $q->fetchOne();

            if (!$sub_county) {
                $sub_county = new Subcounty();
                $sub_county->setName($ward['sub_county']['title']);
                $sub_county->setUuid($ward['sub_county']['id']);

                $sub_county->save();
            }

            if ($found) {
                $found->setName($ward['title']);
                $found->setUuid($ward['id']);
                $found->setSubcountId($sub_county['id']);
                $found->save();
            } else {
                $record = new Ward();

                $record->setName($ward['title']);
                $record->setUuid($ward['id']);
                $record->setSubcountId($sub_county['id']);
                $record->save();
            }
        }
        return $this->renderText(json_encode(array('status' => 'success')));
    }

    public function wards()
    {
        $url = sfConfig::get('app_api_jambo_url') . 'api/v1/county/wards/';

        $stream = new Stream();

        $query_response = $stream->sendRequest([
            'url' => $url,
            'method' => 'GET',
            'ssl' => 'none',
            'contentType' => 'json',
            'headers' => [
                "Authorization" => "JWT {$_SESSION['jambo_token_backend']}",
            ]
        ]);

        if ($query_response->status !== 201 || $query_response->status !== 200) {
            return [];
        }

        return $query_response->content->results;
    }
}