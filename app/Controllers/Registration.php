<?php
namespace  App\Controllers;

use CodeIgniter\Controller;

class Registration extends RestrictedBaseController
{
    public function index()
    {
        if($this->request->getMethod()=='POST')
        {
            $validation = \Config\Services::validation();
            $validation_rules=array('first_name'=>'required|min_length[3]','last_name'=>'required|min_length[3]','email'=>'required|valid_email|is_unique[users.email]','password'=>'required|min_length[8]','password_confirm'=>'required|matches[password]','username'=>'required|min_length[5]|max_length[12]','phone'=>'required|min_length[10]|max_length[10]');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $registration=$this->process_registration($_POST['first_name'],$_POST['last_name'],$_POST['email'],$_POST['password'],$_POST['username'],$_POST['phone']);
                if($registration['error'])
                {
                    $vars['error']=$registration['error'];
                }
            }
            else
            {
                $vars['error']=$validation->listErrors();
            }
        }
        $vars['title']='Registration';
        return view('register/view_register',$vars);



    }
private function process_registration($user_data)
{
    $base_model= new \App\Models\BaseModel();

    $inserted=$base_model->insert("users",$user_data);
    if($inserted)
    {
        return ['success'=>true];
    }
    else
    {
        return ['error'=>'failed to insert data'];
    }


}

}