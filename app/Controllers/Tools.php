<?php


namespace App\Controllers;


use CodeIgniter\Controller;

class Tools extends Controller
{


    public function message($to = 'World')
    {
        echo "Hello {$to}!" . PHP_EOL;
    }

    public function updateAge()
    {
        // is_cli_request() is provided by default input library of codeigniter
        if($this->request->isCLI())
        {
            $this->cron_model->updateAge();
        }
        else
        {
            echo "You dont have access";
        }
    }
}