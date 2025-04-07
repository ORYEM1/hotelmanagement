<?php

namespace App\Controllers;

class Users extends RestrictedBaseController
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
            $table='users';
            $_SESSION["search_{$table}"]=array();
            $_SESSION["search_{$table}_where_in"]=array();
            $_SESSION["search_{$table}_like"]=array();
            if($this->request->getPost('search'))
            {
                $params=array();
                $params['table_name']=$table;
                $params['where']=$where??array();
                $params['where_in']=$where_in??array();
                $params['where_search_fields']=array('id'=>'id','username'=>'username','code'=>'code');
                $params['like_search_fields']=array('date_created'=>'users.date_time_created','phone_number'=>'users.phone_number');
                $params['where_in_search_fields']=array('status'=>'users.status','role'=>'users.role','gender'=>'users.gender');
                $search_range=array();
                $search_range['from_date']=array('column'=>'timestamp','operator'=>'>=');
                $search_range['to_date']=array('column'=>'timestamp','operator'=>'<=');
                $params['search_range']=$search_range;
                $this->set_search_data($params);
            }
            $vars['content_view']='data_table';
            $vars['title']='Users';
            $vars['page_heading']='Users';

            //Data header
            //============================================================================================
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'First Name','sortable'=>true,'db_col_name'=>'first_name');
            $data_header[]=array('name'=>'Other Names','sortable'=>true,'db_col_name'=>'other_names');
            $data_header[]=array('name'=>'Role','sortable'=>false);
            $data_header[]=array('name'=>'Phone Number','sortable'=>false);
            $data_header[]=array('name'=>'Status','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            //============================================================================================
            $data_footer[]=get_advanced_search_button();
            $data_footer[]=get_new_link_button(array('url'=>'/users/new_user','label'=>'New User','icon'=>'fa fa-plus'));
            $vars['data_footer']=$data_footer;

            //Data tables options
            //============================================================================================
            $dt_params=array('ajax'=>'/data_tables/get_data/get_users','bFilter'=>true,'order_columns'=>array('ID'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //Advance search options
            //============================================================================================
            //Search by user ID
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'User ID','name'=>'id','type'=>'number'));
            //Search by username
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Username','name'=>'username','type'=>'text'));
            //Search by phone number
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Phone Number','name'=>'phone_number','type'=>'number'));
            //Search by creation Date Range
            $advanced_search_fields[]=$this->advanced_search->get_date_time_range(array('label'=>'Date Created'));
            //Search by user status
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Status','name'=>'status','options'=>get_statuses_array()));
            //Search by user role
            $options=$this->base_model->get_form_options(array('table'=>'user_roles','order'=>array('role'=>'asc')),'id','role');
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Role','name'=>'role','options'=>$options));
            //Search by gender
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Gender','name'=>'gender','options'=>get_genders_array()));
            $vars['advanced_search_fields']=$advanced_search_fields;
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view('page',$vars);
    }
    public function view_user($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id', 'role','theme', 'allowed_countries', 'date_time_created', 'first_name', 'other_names', 'gender', 'phone_number', 'email', 'username', 'password', 'whitelist_ip', 'allowed_ip_id', 'status', 'comment', 'user_roles.role', 'user_roles.role_type',"CONCAT(cb.first_name,' ',cb.other_names) AS created_by");
            $join[]=array('table'=>'user_roles','condition'=>'users.role=user_roles.id','type'=>'left');
            $join[]=array('table'=>'users cb','condition'=>'users.created_by=cb.id','type'=>'left');
            $user_data=$this->base_model->get_data(array('table'=>'users','fields'=>$fields,'join'=>$join,'where'=>array('users.id'=>$id)),true);
            if(empty($user_data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['page_heading']= $user_data['first_name'].' '.$user_data['other_names'];
                $vars['record']=$user_data;
                $vars['statuses']=get_statuses_array(true);
                $vars['allowed_countries']=empty($user_data['allowed_countries'])?array():$this->base_model->get_data_in(array('table'=>'countries','fields'=>array('id','country'),'where_in'=>array('id'=>explode(',',$user_data['allowed_countries'])),'order'=>'country'));
                $vars['allowed_ip_pool']=empty($user_data['allowed_ip_id'])?array():$this->base_model->get_data_in(array('table'=>'whitelisted_user_ips','fields'=>array('id','pool'),'where_in'=>array('id'=>explode(',',$user_data['allowed_ip_id']))),true);
                $vars['content_view']='users/view_user';
                $vars['title']='User Data';
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_user($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit user')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('first_name' =>'required','other_names' =>'required','username' =>'required','role' =>'required','status' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_users=$this->base_model->get_data(array('table'=>'users','where'=>array('username'=>$this->request->getPost('username'))));
                if(count($existing_users)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The username {$this->request->getPost('username')} is already assigned to another user")));
                }
                else if(isset($existing_users[0]['id'])&&($existing_users[0]['id']!=$id))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The username {$this->request->getPost('username')} is already assigned to another user")));
                }

                $db_user_data=$this->base_model->get_data(array('table'=>'users','where'=>array('id'=>$id)),true);
                if(!user_has_permission('change user role')&&$db_user_data['role']!=$this->request->getPost('role'))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"You do not have permission to change user role")));
                }
                $user_role_data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$_SESSION['user_data']['role'])),true);
                if(isset($user_role_data['role_type'])&&strtolower($user_role_data['role_type'])=='basic')
                {
                    exit(json_encode(array('status'=>0,'msg'=>"Your role type cannot edit a user account")));
                }
                $db_user_role_data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$db_user_data['role']),true),true);
                if(strtolower($db_user_role_data['role_type'])=='admin' && strtolower($user_role_data['role_type'])!='admin')
                {
                    exit(json_encode(array('status'=>0,'msg'=>"Your role type cannot edit an admin user account")));
                }
                if(isset($user_role_data['role_type'])&&strtolower($user_role_data['role_type'])!='admin')
                {
                    $assigned_role_id=$this->request->getPost('role');
                    if(empty($assigned_role_id))
                    {
                        exit(json_encode(array('status'=>0,'msg'=>'You must assign a role to the user')));
                    }
                    $assigned_role=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$assigned_role_id)),true);
                    if(empty($assigned_role))
                    {
                        exit(json_encode(array('status'=>0,'msg'=>'Assigned role not found')));
                    }
                    if(strtolower($assigned_role['role_type'])!='basic')
                    {
                        exit(json_encode(array('status'=>0,'msg'=>'Your account can only edit and assign basic roles')));
                    }
                }
                
                $user_data=array();
                foreach($_POST as $key=>$value)
                {
                    if(empty($value))
                    {
                        $user_data[$key]=null;
                    }
                    else if(is_array($value))
                    {
                        $user_data[$key]=implode(',',$value);
                    }
                    else
                    {
                        $user_data[$key]=$value;
                    }
                }
                $this->base_model->update_data(array('table'=>'users','where'=>array('id'=>$id),'data'=>$user_data),true);
                exit(json_encode(array('status'=>1,'msg'=>"User account updated successfully")));
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
                $user_data=$this->base_model->get_data(array('table'=>'users','where'=>array('id'=>$id)),true);
                if(empty($user_data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['first_name']=array('field_type'=>'text_field','label'=>'First Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$user_data['first_name']??'');
                    $config['other_names']=array('field_type'=>'text_field','label'=>'Other Names','type'=>'text','required'=>'required','value'=>$user_data['other_names']??'');
                    $config['gender']=array('field_type'=>'select_field','label'=>'Gender','required'=>'required','options'=>get_genders_array(),'value'=>$user_data['gender']??'');
                    $config['phone_number']=array('field_type'=>'text_field','label'=>'Phone Number','type'=>'text','value'=>$user_data['phone_number']??'');
                    $config['email']=array('field_type'=>'text_field','label'=>'Email','type'=>'email','value'=>$user_data['email']??'');
                    $config['username']=array('field_type'=>'text_field','label'=>'Username','required'=>'required','type'=>'text','value'=>$user_data['username']??'');
                    $options=$this->base_model->get_form_options(array('table'=>'user_roles','order'=>array('role'=>'asc')),'id','role');
                    $config['role']=array('field_type'=>'select_field','label'=>'Role','options'=>$options,'value'=>$user_data['role']??'');
                    $config['status']=array('field_type'=>'select_field','label'=>'Status','required'=>'required','options'=>get_statuses_array(),'value'=>$user_data['status']??'');
                    $config['whitelist_ip']=array('field_type'=>'select_field','label'=>'IP Whitelisting','required'=>'required','options'=>get_statuses_array(),'value'=>$user_data['whitelist_ip']??'');
                    $options=$this->base_model->get_form_options(array('table'=>'whitelisted_user_ips','order'=>array('name'=>'asc')),'id','name');
                    $config['allowed_ip_id']=array('field_type'=>'select_field','label'=>'IP Pool','options'=>$options,'value'=>$user_data['allowed_ip_id']??'');
                    $options=$this->base_model->get_form_options(array('table'=>'countries','where'=>array('supported'=>1),'order'=>array('country'=>'asc')),'id','country');
                    $config['allowed_countries']=array('field_type'=>'checklist','label'=>'Allowed Countries','options'=>$options,'value'=>$user_data['allowed_countries']??'');
                    $config['theme']=array('field_type'=>'select_field','label'=>'Theme','required'=>'required','options'=>get_themes(),'value'=>$user_data['theme']??'');
                    $config['comment']=array('field_type'=>'textarea','label'=>'Comments','type'=>'text','value'=>$user_data['comment']??'','cols'=>300,'rows'=>3);
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit User';
                    $vars['submit_url']= "/users/edit_user/{$user_data['id']}";
                    $vars['content_view']='form';
                    $vars['title']='Edit User';
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
    public function new_user($load_type='')
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to add user')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('first_name' =>'required','other_names' =>'required','username' =>'required','password' =>'required|min_length[7]','role' =>'required','status' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_users=$this->base_model->get_data(array('table'=>'users','where'=>array('username'=>$this->request->getPost('username'))));
                if(!empty($existing_users))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The username {$this->request->getPost('username')} is already assigned to another user")));
                }

                $user_role_data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$_SESSION['user_data']['role'])),true);
                if(isset($user_role_data['role_type'])&&strtolower($user_role_data['role_type'])=='basic')
                {
                    exit(json_encode(array('status'=>0,'msg'=>"Your role type cannot create a user account")));
                }
                if(isset($user_role_data['role_type'])&&strtolower($user_role_data['role_type'])!='admin')
                {
                    $assigned_role_id=$this->request->getPost('role');
                    if(empty($assigned_role_id))
                    {
                        exit(json_encode(array('status'=>0,'msg'=>'You must assign a role to the user')));
                    }
                    $assigned_role=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$assigned_role_id)),true);
                    if(empty($assigned_role))
                    {
                        exit(json_encode(array('status'=>0,'msg'=>'Assigned role not found')));
                    }
                    if(strtolower($assigned_role['role_type'])!='basic')
                    {
                        exit(json_encode(array('status'=>0,'msg'=>'Your account cannot assign the selected role')));
                    }

                }
                $user_data=array();
                foreach($_POST as $key=>$value)
                {
                    if(empty($value))
                    {
                        $user_data[$key]=null;
                    }
                    else if(is_array($value))
                    {
                        $user_data[$key]=implode(',',$value);
                    }
                    else
                    {
                        $user_data[$key]=$value;
                    }
                }
                $user_data['password']=sha1($user_data['password']);
                $date=date('Y-m-d');
                $time=date('H:i:s');
                $user_data['date_time_created']=$date.' '.$time;
                $user_data['created_by']=$_SESSION['user_data']['id'];
                $id=$this->base_model->insert_data('users',$user_data);
                exit(json_encode(array('status'=>1,'msg'=>"User account created successfully. ID:{$id}")));
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
                $config['first_name']=array('field_type'=>'text_field','label'=>'First Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['first_name']??'');
                $config['other_names']=array('field_type'=>'text_field','label'=>'Other Names','type'=>'text','required'=>'required','value'=>$_POST['other_names']??'');
                $config['gender']=array('field_type'=>'select_field','label'=>'Gender','required'=>'required','options'=>get_genders_array(),'value'=>$_POST['gender']??'');
                $config['phone_number']=array('field_type'=>'text_field','label'=>'Phone Number','type'=>'text','value'=>$_POST['phone_number']??'');
                $config['email']=array('field_type'=>'text_field','label'=>'Email','type'=>'email','value'=>$_POST['email']??'');
                $config['username']=array('field_type'=>'text_field','label'=>'Username','required'=>'required','type'=>'text','value'=>$_POST['username']??'');
                $config['password']=array('field_type'=>'password_field','label'=>'Password','required'=>'required','type'=>'password','value'=>$_POST['password']??'','minlength'=>'5');
                $options=$this->base_model->get_form_options(array('table'=>'user_roles','order'=>array('role'=>'asc')),'id','role');
                $config['role']=array('field_type'=>'select_field','label'=>'Role','options'=>$options,'value'=>$_POST['role']??'');
                $config['status']=array('field_type'=>'select_field','label'=>'Status','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['status']??'');
                $config['whitelist_ip']=array('field_type'=>'select_field','label'=>'IP Whitelisting','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['whitelist_ip']??'');
                $options=$this->base_model->get_form_options(array('table'=>'whitelisted_user_ips','order'=>array('name'=>'asc')),'id','name');
                $config['allowed_ip_id']=array('field_type'=>'select_field','label'=>'IP Pool','options'=>$options,'value'=>$_POST['allowed_ip_id']??'');
                $options=$this->base_model->get_form_options(array('table'=>'countries','where'=>array('supported'=>1),'order'=>array('country'=>'asc')),'id','country');
                $config['allowed_countries']=array('field_type'=>'checklist','label'=>'Allowed Countries','options'=>$options,'value'=>$_POST['allowed_countries']??'');
                $config['theme']=array('field_type'=>'select_field','label'=>'Theme','required'=>'required','options'=>get_themes(),'value'=>$_POST['theme']??'');
                $config['comment']=array('field_type'=>'textarea','label'=>'Comments','type'=>'text','value'=>$_POST['comment']??'','cols'=>300,'rows'=>3);
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New User';
                $vars['submit_url']="/users/new_user";
                $vars['content_view']='form';
                $vars['title']='New User';

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
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to reset user password')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('password' =>'required|min_length[7]','confirm_password' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                if($this->request->getPost('password')!=$this->request->getPost('confirm_password'))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The passwords entered have to be the same")));
                }
                $db_user_data=$this->base_model->get_data(array('table'=>'users','where'=>array('id'=>$id)),true);
                $user_role_data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$_SESSION['user_data']['role'])),true);
                $db_user_role_data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$db_user_data['role']),true),true);
                if(strtolower($db_user_role_data['role_type'])=='admin' && strtolower($user_role_data['role_type'])!='admin')
                {
                    exit(json_encode(array('status'=>0,'msg'=>"Your role type cannot edit an admin user account")));
                }
                $password=sha1($this->request->getPost('password'));
                $user_data=array('password'=>$password);
                $this->base_model->update_data(array('table'=>'users','where'=>array('id'=>$id),'data'=>$user_data),true);
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
                $query_params=array('table'=>'users','where'=>array('id'=>$id));
                $user_data=$this->base_model->get_data($query_params,true);
                if(empty($user_data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['password']=array('field_type'=>'password_field','label'=>'New Password','autofocus'=>'autofocus','required'=>'required','type'=>'password','value'=>$_POST['password']??'','minlength'=>'7');
                    $config['confirm_password']=array('field_type'=>'password_field','label'=>'Confirm Password','required'=>'required','type'=>'password','value'=>$_POST['password']??'','minlength'=>'7');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Reset Password for '.$user_data['first_name'].' '.$user_data['other_names'];
                    $vars['submit_url']= base_url("users/reset_password/{$user_data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Reset User Password';
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
    public function change_password()
    {
        if($this->request->getPost('submit'))
        {
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to change password')));
            }
            unset($_POST['submit']);
            $validation = \Config\Services::validation();
            $validation_rules=array('old_password' =>'required','password' =>'required|min_length[7]','confirm_password' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                if(sha1($this->request->getPost('old_password'))!=$_SESSION['user_data']['password'])
                {
                    exit(json_encode(array('status'=>0,'msg'=>"Incorrect old password")));
                }
                if($this->request->getPost('password')!=$this->request->getPost('confirm_password'))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The passwords entered have to be the same")));
                }
                $password=sha1($this->request->getPost('password'));
                $user_data=array('password'=>$password);
                $this->base_model->update_data(array('table'=>'users','where'=>array('id'=>$_SESSION['user_data']['id']),'data'=>$user_data),true);
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
                $config=array();
                $config['old_password']=array('field_type'=>'password_field','label'=>'Old Password','autofocus'=>'autofocus','required'=>'required','type'=>'password','value'=>$_POST['password']??'');
                $config['password']=array('field_type'=>'password_field','label'=>'New Password','required'=>'required','type'=>'password','value'=>$_POST['password']??'','minlength'=>'7');
                $config['confirm_password']=array('field_type'=>'password_field','label'=>'Confirm Password','required'=>'required','type'=>'password','value'=>$_POST['password']??'','minlength'=>'7');
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='Change Password';
                $vars['submit_url']= base_url("users/change_password");
                $vars['content_view']='form';
                $vars['title']='Change Password';
            }
            else
            {
                $vars['content_view']='unauthorized';
                $vars['title']='401 Unauthorized';
            }
            return view($vars['content_view'],$vars);
        }
    }
    public function change_theme()
    {
        if($this->request->getPost('submit'))
        {
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to change password')));
            }
            unset($_POST['submit']);
            $validation = \Config\Services::validation();
            $validation_rules=array('theme' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $user_data=array('theme'=>trim($_POST['theme']));
                $this->base_model->update_data(array('table'=>'users','where'=>array('id'=>$_SESSION['user_data']['id']),'data'=>$user_data),true);
                $_SESSION['theme']=$_POST['theme'];
                exit(json_encode(array('status'=>1,'msg'=>"Theme updated successfully")));
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
                $user_data=$this->base_model->get_data(array('table'=>'users','where'=>array('id'=>$_SESSION['user_data']['id'])),true);
                $config['theme']=array('field_type'=>'select_field','label'=>'Theme','required'=>'required','options'=>get_themes(),'value'=>$user_data['theme']??'');
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='Change Theme';
                $vars['submit_url']= base_url("users/change_theme");
                $vars['content_view']='form';
                $vars['title']='Change Theme';
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
