<?php
namespace App\Controllers;

class HomeController extends Controller 
{
    public function index() 
    {
        $this->render(
            'welcome',
            [
                'title' => 'Bienvenue sur SysGET',
                'user' => 'Benito HIIPS'
            ]
        );
    }
}