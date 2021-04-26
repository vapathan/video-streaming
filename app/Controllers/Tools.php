<?php


namespace App\Controllers;



class Tools extends BaseController
{

    public function index()
    {
        $client = \Config\Services::curlrequest();

        $response = $client->request('GET', 'http://localhost/video-streaming/tools/message/123', [
        ]);

        print_r($response->getBody());
    }

    public function message($to = 'World')
    {
        $output=null;
        $retval=null;
        exec('php index.php tools hello Ram', $output, $retval);
        echo "Returned with status $retval and output:\n";
        print_r($output);
    }


    public function hello($name){
        echo "Hello ". $name;
    }

}