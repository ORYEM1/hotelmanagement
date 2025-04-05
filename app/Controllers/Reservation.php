<?php
namespace  App\Controllers;

class Reservation extends BaseController
{
    public function index()
    {
        $vars['title']='Home';
        return view('page',$vars);
    }
}