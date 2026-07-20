<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function choix(): string
    {
        return view('choix');
    }
}
