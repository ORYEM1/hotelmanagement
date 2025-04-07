<?php

namespace App\Controllers;

class Collection_transactions extends RestrictedBaseController
{
    public function index()
    {
        $vars['title'] = 'Home';
        return view('page',$vars);
    }
}
