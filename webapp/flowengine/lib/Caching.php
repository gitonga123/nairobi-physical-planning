<?php
// require "predis/autoload.php";
// Predis\Autoloader::register();

class Caching
{
    private $file_name   =   null;
    private $invoices = 0;
    private $declined = 0;


    private $permits = 0;

    private $file_content = [];

    public function __construct($file_name, $invoices = 0, $declined = 0, $permits = 0)
    {
        // $this->file_name  =  "./" . $file_name . ".json";
        // $this->declined = $declined;
        // $this->invoices = $invoices;
        // $this->permits = $permits;

        // $this->fileExists();
        // $this->file_content = $this->readCacheContent();
        try {
            $redis = new Predis\Client();
            $redis->set("hello_world", "Hi from PHP!");
            $value = $redis->get("hello_world");
            var_dump($value);
        } catch (Exception $e) {
            echo "Couldn't connect to Redis";
            echo $e->getMessage();
        }
    }

    private function fileExists()
    {
        if (!file_exists($this->file_name)) {
            fopen($this->file_name, "w");
            fclose($this->file_name);
        }
    }

    public function readCacheContent()
    {
        $content =  file_get_contents($this->file_name);
        return json_decode($content);
    }

    public function writeCacheContent()
    {
        $this->create_content();
        $fp = fopen($this->file_name, 'w');
        fwrite($fp, json_encode($this->file_content));
        fclose($fp);
    }

    private function create_content()
    {
        $this->file_content = [
            'invoices' => $this->invoices,
            'declined' => $this->declined,
            'permits' => $this->permits,
            'invoice_list' => [],
            'permit_list' => []
        ];
    }

    public function isToUpdateCache()
    {
        $content = $this->readCacheContent();
        if ($content['invoices'] != $this->invoices) {
            return true;
        }

        if ($content['permits'] != $this->invoices) {
            return true;
        }

        return false;
    }

    public function addInvoicesById($invoice_id)
    {
        $this->file_content['invoice_list'][$invoice_id] = 1;
    }

    public function addPermitsById($permit_id)
    {
        $this->file_content['permit_list'][$permit_id] = 1;
    }
}
