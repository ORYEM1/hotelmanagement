<?php
namespace App\Controllers;



class Login extends BaseController
{
    public function index()
    {
        if($this->request->getPost('username'))
        {
            $validation =\Config\Services::validation();
            $validation_rules=array('username'=>'required','password'=>'required|min_length[8]');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $login =$this->process_login($_POST['username'],$_POST['password']);
                if($login['error'])
                {
                    $vars['error']=$login['error'];
                }
            }
            else
            {
                $vars['error']=$validation->listErrors();

            }
        }
        $vars['title']='login';
        return view('login/view_login',$vars);
    }
}

