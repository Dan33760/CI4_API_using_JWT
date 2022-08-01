<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class SendMail extends ResourceController
{
    public function index()
    {
        //
    }

    function sendMail() { 
        // $to = 'fongolapos@gmail.com';
        // $subject = 'subject Test';
        // $message = 'message Test';
        $to = $this->request->getVar('mailTo');
        $subject = $this->request->getVar('subject');
        $message = $this->request->getVar('message');
        
        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setFrom('dansivyolo@gmail.com', 'Confirm Registration');
        
        $email->setSubject($subject);
        $email->setMessage($message);
        if ($email->send()) 
		{
            $data = ['Message' => 'Email successfully sent'];
            return $this->getResponse($data, ResponseInterface::HTTP_OK);
        } 
		else 
		{
            return $data = $email->printDebugger(['headers']);
            // print_r($data);
        }
    }
}
