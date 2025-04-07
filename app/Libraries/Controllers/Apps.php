<?php

namespace App\Controllers;

class Apps extends RestrictedBaseController
{
    private string $controller;
    public function __construct()
    {
        $this->controller = strtolower((new \ReflectionClass($this))->getShortName());
    }
    public function index()
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $table='apps';
            $_SESSION["search_{$table}"]=array();
            $_SESSION["search_{$table}_where_in"]=array();
            $_SESSION["search_{$table}_like"]=array();
            if($this->request->getPost('search'))
            {
                $params=array();
                $params['table_name']=$table;
                $params['where']=$where??array();
                $params['where_in']=$where_in??array();
                $params['where_search_fields']=array('id'=>'id','login_name'=>'login_name');
                $params['where_in_search_fields']=array('status'=>'status','whitelist_ip'=>'whitelist_ip');
                $this->set_search_data($params);
            }

            $vars['content_view']='data_table';
            $vars['title']='Apps';
            $vars['page_heading']='Apps';

            //Data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Date & Time Created','sortable'=>true,'db_col_name'=>'timestamp');
            $data_header[]=array('name'=>'App NAme','sortable'=>true,'db_col_name'=>'app_name');
            $data_header[]=array('name'=>'Login Name','sortable'=>false);
            $data_header[]=array('name'=>'IP whitelisting','sortable'=>false);
            $data_header[]=array('name'=>'Status','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            //============================================================================================
            $data_footer[]=get_advanced_search_button();
            $data_footer[]=get_new_link_button(array('url'=>"/apps/new_app",'label'=>'New App','icon'=>'fa fa-plus'));
            $vars['data_footer']=$data_footer;

            //Data tables options
            //============================================================================================
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_apps'),'bFilter'=>true,'order_columns'=>array('App Name'=>'asc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //Advance search options
            //============================================================================================
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'App ID','name'=>'id','type'=>'number'));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Login Name','name'=>'login_name','type'=>'text'));
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Status','name'=>'status','options'=>get_statuses_array()));
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'IP Whitelisting','name'=>'whitelist_ip','options'=>get_statuses_array()));
            $vars['advanced_search_fields']=$advanced_search_fields;
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view('page',$vars);
    }
    public function view_app($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id', 'timestamp', 'user_id', 'app_name', 'login_name', 'login_password', 'whitelist_ip', 'allowed_ip', 'status', 'allowed_transactions', 'default_notify_url', 'always_send_callback', 'use_callback_gateway', 'callback_gateway', 'contact_name', 'contact_number', 'contact_email', 'log_ipn_requests', "CONCAT(users.first_name,' ',users.other_names) AS created_by");
            $join[]=array('table'=>'users','on'=>'users.id=apps.user_id','type'=>'left');
            $data=$this->base_model->get_data(array('table'=>'apps','join'=>$join,'fields'=>$fields,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='apps/view_app';
                $vars['title']='App Details';
                $vars['page_heading']='App Details';
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_app($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'apps','where'=>array('id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit app')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array('app_name' =>'required','login_name' =>'required','status'=>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'apps','where'=>array('login_name'=>$this->request->getPost('login_name'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The login name {$this->request->getPost('login_name')} is already in the database")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The login name {$this->request->getPost('login_name')} is already assigned to another app")));
                }

                $existing_data=$this->base_model->get_data(array('table'=>'apps','where'=>array('app_name'=>$this->request->getPost('app_name'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The app name {$this->request->getPost('app_name')} is already in the database")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The app name {$this->request->getPost('app_name')} is already assigned to another app")));
                }

                if(!empty($_POST['allowed_ip']))
                {
                    $_POST['allowed_ip']=str_replace(' ','',$_POST['allowed_ip']);
                }
                $data=array();
                foreach($_POST as $key=>$value)
                {
                    if(empty($value) && $value!=0)
                    {
                        $data[$key]=null;
                    }
                    else
                    {
                        if(is_array($value))
                        {
                            $data[$key]=implode(',',$value);
                        }
                        else
                        {
                            $data[$key]=$value;
                        }
                    }
                }
                
                $this->base_model->update_data(array('table'=>'apps','where'=>array('id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"App updated successfully")));
            }
            else
            {
                exit(json_encode(array('status'=>0,'msg'=>$validation->listErrors())));
            }
            
        }
        else
        {
            if(user_has_access($this->controller,__FUNCTION__))
            {
                $data=$this->base_model->get_data(array('table'=>'apps','where'=>array('id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['app_name']=array('field_type'=>'text_field','label'=>'App Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$data['app_name']??'');
                    $config['login_name']=array('field_type'=>'text_field','label'=>'Login Name','type'=>'text','required'=>'required','value'=>$data['login_name']??'');
                    $config['whitelist_ip']=array('field_type'=>'select_field','label'=>'IP Whitelisting','required'=>'required','options'=>get_statuses_array(),'value'=>$data['whitelist_ip']??'');
                    $config['allowed_ip']=array('field_type'=>'text_field','label'=>'Allowed IPs','type'=>'text','value'=>$data['allowed_ip']??'');
                    $config['status']=array('field_type'=>'select_field','label'=>'Status','required'=>'required','options'=>get_statuses_array(),'value'=>$data['status']??'');
                    $config['allowed_transactions']=array('field_type'=>'checklist','label'=>'Allowed Transactions','options'=>get_transaction_types(),'value'=>$data['allowed_transactions']??'');
                    $config['default_notify_url']=array('field_type'=>'text_field','label'=>'Default Callback URL','type'=>'url','value'=>$data['default_notify_url']??'');
                    $config['always_send_callback']=array('field_type'=>'select_field','label'=>'Always Send Callback','required'=>'required','options'=>get_statuses_array(),'value'=>$data['always_send_callback']??'');
                    $config['use_callback_gateway']=array('field_type'=>'select_field','label'=>'Use Callback Gateway','required'=>'required','options'=>get_statuses_array(),'value'=>$data['use_callback_gateway']??'');
                    $config['callback_gateway']=array('field_type'=>'text_field','label'=>'Callback Gateway URL','type'=>'url','value'=>$data['callback_gateway']??'');
                    $config['contact_name']=array('field_type'=>'text_field','label'=>'Contact Person','type'=>'text','value'=>$data['contact_name']??'');
                    $config['contact_number']=array('field_type'=>'text_field','label'=>'Contact Number','type'=>'text','value'=>$data['contact_number']??'');
                    $config['contact_email']=array('field_type'=>'text_field','label'=>'Contact Email','type'=>'email','value'=>$data['contact_email']??'');
                    $config['log_ipn_requests']=array('field_type'=>'select_field','label'=>'Log IPN Requests','required'=>'required','options'=>get_statuses_array(),'value'=>$data['log_ipn_requests']??'');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit App';
                    $vars['submit_url']= base_url("apps/edit_app/{$data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Edit App';
                }
            }
            else
            {
                $vars['content_view']='unauthorized';
                $vars['title']='401 Unauthorized';
            }
            return view($vars['content_view'],$vars);
        }
    }
    public function new_app()
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission required to add an app')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('app_name' =>'required','login_name' =>'required','status'=>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'apps','where'=>array('login_name'=>$this->request->getPost('login_name'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The login name {$this->request->getPost('login_name')} is already assigned to another app")));
                }
                $existing_data=$this->base_model->get_data(array('table'=>'apps','where'=>array('app_name'=>$this->request->getPost('app_name'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The app name {$this->request->getPost('app_name')} is already assigned to another app")));
                }
                if(!empty($_POST['allowed_ip']))
                {
                    $_POST['allowed_ip']=str_replace(' ','',$_POST['allowed_ip']);
                }
                $data=array();
                foreach($_POST as $key=>$value)
                {
                    if(empty($value) && $value!=0)
                    {
                        $data[$key]=null;
                    }
                    else
                    {
                        if(is_array($value))
                        {
                            $data[$key]=implode(',',$value);
                        }
                        else
                        {
                            $data[$key]=$value;
                        }
                    }
                }
                $data['user_id']=$_SESSION['user_data']['id'];
                $id=$this->base_model->insert_data('apps',$data);
                exit(json_encode(array('status'=>1,'msg'=>"App added successfully. ID:{$id}")));
            }
            else
            {
                exit(json_encode(array('status'=>0,'msg'=>$validation->listErrors())));
            }
            
        }
        else
        {
            if(user_has_access($this->controller,__FUNCTION__))
            {
                $config=array();
                $config['app_name']=array('field_type'=>'text_field','label'=>'App Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['app_name']??'');
                $config['login_name']=array('field_type'=>'text_field','label'=>'Login Name','type'=>'text','required'=>'required','value'=>$_POST['login_name']??'');
                $config['login_password']=array('field_type'=>'password_field','label'=>'Login Password','type'=>'password','required'=>'required','value'=>$_POST['login_password']??'');
                $config['whitelist_ip']=array('field_type'=>'select_field','label'=>'IP Whitelisting','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['whitelist_ip']??'');
                $config['allowed_ip']=array('field_type'=>'text_field','label'=>'Allowed IPs','type'=>'text','value'=>$_POST['allowed_ip']??'');
                $config['status']=array('field_type'=>'select_field','label'=>'Status','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['status']??'');
                $config['allowed_transactions']=array('field_type'=>'checklist','label'=>'Allowed Transactions','options'=>get_transaction_types(),'value'=>$_POST['allowed_transactions']??'');
                $config['default_notify_url']=array('field_type'=>'text_field','label'=>'Default Callback URL','type'=>'url','value'=>$_POST['default_notify_url']??'');
                $config['always_send_callback']=array('field_type'=>'select_field','label'=>'Always Send Callback','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['always_send_callback']??'');
                $config['use_callback_gateway']=array('field_type'=>'select_field','label'=>'Use Callback Gateway','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['use_callback_gateway']??'');
                $config['callback_gateway']=array('field_type'=>'text_field','label'=>'Callback Gateway URL','type'=>'url','value'=>$_POST['callback_gateway']??'');
                $config['contact_name']=array('field_type'=>'text_field','label'=>'Contact Person','type'=>'text','value'=>$_POST['contact_name']??'');
                $config['contact_number']=array('field_type'=>'text_field','label'=>'Contact Number','type'=>'text','value'=>$_POST['contact_number']??'');
                $config['contact_email']=array('field_type'=>'text_field','label'=>'Contact Email','type'=>'email','value'=>$_POST['contact_email']??'');
                $config['log_ipn_requests']=array('field_type'=>'select_field','label'=>'Log IPN Requests','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['log_ipn_requests']??'');
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New App';
                $vars['submit_url']= base_url("apps/new_app");
                $vars['content_view']='form';
                $vars['title']='New App';
            }
            else
            {
                $vars['content_view']='unauthorized';
                $vars['title']='401 Unauthorized';
            }
            return view($vars['content_view'],$vars);
        }
    }

    public function reset_password($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to reset app password')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('login_password' =>'required|min_length[7]','confirm_password' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                if($_POST['login_password']!=$_POST['confirm_password'])
                {
                    exit(json_encode(array('status'=>0,'msg'=>'The confirm password and new password do not match')));
                }
                $password=sha1($this->request->getPost('login_password'));
                $update_data=array('login_password'=>$password);
                $this->base_model->update_data(array('table'=>'apps','where'=>array('id'=>$id),'data'=>$update_data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Password updated successfully")));
            }
            else
            {
                exit(json_encode(array('status'=>0,'msg'=>$validation->listErrors())));
            }

        }
        else
        {
            if(user_has_access($this->controller,__FUNCTION__))
            {
                $query_params=array('table'=>'apps','where'=>array('id'=>$id));
                $data=$this->base_model->get_data($query_params,true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['login_password']=array('field_type'=>'password_field','label'=>'New Password','autofocus'=>'autofocus','required'=>'required','type'=>'password','value'=>$_POST['login_password']??'','minlength'=>'7');
                    $config['confirm_password']=array('field_type'=>'password_field','label'=>'Confirm Password','required'=>'required','type'=>'password','value'=>$_POST['password']??'','minlength'=>'7');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Reset Password for '.$data['app_name'];
                    $vars['submit_url']= base_url("apps/reset_password/{$data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Reset App Password';
                }
            }
            else
            {
                $vars['content_view']='unauthorized';
                $vars['title']='401 Unauthorized';
            }
            return view($vars['content_view'],$vars);
        }
    }

}