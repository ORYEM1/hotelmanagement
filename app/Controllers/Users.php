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
        if(user_has_access($this->controller)) {
            $table ='users';
            $_SESSION["search_{$table}"]=array();
            $_SESSION["search_{$table}_where_in"]=array();
            $_SESSION["search_{$table}_like"]=array();
            $_SESSION["search_{$table}_where"]=array();
            $_SESSION["search_{$table}_like_where"]=array();
            if($this->request->getPost('search'))
            {
                $params=array();
                $params['table_name']=$table;
                $params['where']=$where??array();
                $params['like']=$like??array();
                $params['where_in']=$where_in??array();
                $params['where_search_fields']=array('id'=>'id','username'=>'username','email'=>'email');
                $params['like_search_fields']=array('date_created'=>'users.date_time_created','phone_number'=>'users.phone_number','email'=>'users.email');
                $params['where_like_search_fields']=array('status'=>'users.status','role'=>'users.role');
                $search_range=array();
                $search_range['from_date']=array('column'=>'operator'=>'>=');
                $search_range['to_date']=array('column'=>'operator'=>'<=');
                $search_params['search_range']=$search_range;
                $params['search']=$search_params['search'];
                $this->set_search_data($params);
            }
            $vars['content_view']='data_table';
            $vars['title']='Users';
            $vars['page_heading']='Users';

            //Data header
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('firstname'=>'First Name','sortable'=>true,'db_col_name'=>'first_name');
            $data_header[]=array('name'=>'Last Name','sortable'=>true,'db_col_name'=>'last_name');
            $data_header[]=array('name'=>'Username','sortable'=>true,'db_col_name'=>'username');
            $data_header[]=array('name'=>'Email','sortable'=>false,'db_col_name'=>'email');
            $data_header[]=array('name'=>'Role','sortable'=>false,'db_col_name'=>'role');
            $vars['data_header']=$data_header;

            //Data table options
            $dt_params=array('ajax'=>'/data_tables/get_data/get_users','bFilter'=>true,'order_columns'=>array('First Name'=>'ASC'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //advanced search


        }
        else
        {
            $vars['content_view']='access_denied';
            $vars['title']='Access Denied';
        }
        return view('page',$vars);
    }
    public function view_user($id=0)
    {
        if(user_has_access($this->controller)) {
            $fields=array('id','role','first_name','last_name','username','email','phone_number','date_created','date_updated');
            $join[]=array('table'=>'user_roles','condition'=>'users.id=user_roles.user_id','type'=>'left');
            $user_data=$this->base_model->get_data(array('table'=>'users','fields'=>$fields,'join'=>$join,'where'=>array('users.id'=>$id)),assoc:true);
            if(empty($user_data))
            {
                $vars['content_view']='not_found';
                $vars['title']='User Not Found';
            }
            else
            {
                $vars['page_headding']=$user_data['first_name'].' '.$user_data['last_name'];
                $vars['record']=$user_data;
                $vars['statuses']=get_statuses_array(flip: true);
                $vars['content_view']='users/view_user';
                $vars['title']='User Details';
            }
        }
        else
        {
            $vars['content_view']='access_denied';
            $vars['title']='Access Denied';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_user($id=0)
    {
        if($this->request->getPost('submit')) {
            unset($_POST['submit']);
            if(!user_has_access($this->controller))
            {
                exit(json_encode(array('status'=>0,'msg'=>'Access Denied')));
            }
            $validation =\Config\Services::validation();
            $validation_rule=array('first_name'=>'required','last_name'=>'required','username'=>'required');
            $validation->setRules($validation_rule);
            if($validation->withRequest($this->request)->run()) {
                $existing_users=$this->base_model->get_data(array('table'=>'users','where'=>array('username'=>$this->request->getPost('username')));
                if(count($existing_users)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>'Username already taken')));

                }
                elseif (isset($existing_users['id'])&&($existing_users['id']!=$id))
                {
                    exit(json_encode(array('status'=>0,'msg'=>'Username already taken')));
                }
                $db_user_data=$this->base_model->get_data(array('table'=>'users','where'=>array('id'=>$id)),assoc:true);
                if(!user_has_permission('change user role')&&$db_user_data['role']!=1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>'You are not allowed to change user role')));
                }
                $db_user_role_data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$_SESSION['user_data']['role'])),assoc:true);
                if(isset($user_role_data['role_type'])&&strtolower($user_role_data['role_type'])=='basic')
                {
                    exit(json_encode(array('status'=>0,'msg'=>'You are not allowed to change user role')));
                }
                $assigned_role=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$id)),assoc:true);
                if(empty($assigned_role))
                {
                    exit(json_encode(array('status'=>0,'msg'=>'Assigned role not found')));
                }


            }
            $user_data=array();
            foreach($_POST as $key=>$value)
            {
                if(empty($value))
                {
                    unset($_POST[$key]);
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
            $this->base_model->update_data(array('table'=>'users','where'=>array('id'=>$id)),$user_data);
            exit(json_encode(array('status'=>1,'msg'=>'User details saved')));
        }
        else
        {
            if(user_has_access($this->controller)) {
                $user_data=$this->base_model->get_data(array('table'=>'users','where'=>array('id'=>$id)),assoc:true);
                if(empty($user_data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='User Not Found';
                }
                else
                {
                    $config=array();
                    $config['first_name']=array('field_type'=>'text','label'=>'First Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$user_data['first_name']??'');
                    $config['last_name']=array('field_type'=>'text','label'=>'Last Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$user_data['last_name']??'');
                    $config['gender']=array('field_type'=>'select_field','label'=>'Gender','type'=>'select','required'=>'required','option'=>get_genders_array(),'value'=>$user_data['gender']??'');
                    $config['phone_number']=array('field_type'=>'text_field','label'=>'Phone Number','type'=>'text','required'=>'required','value'=>$user_data['phone_number']??'');
                    $config['email']=array('field_type'=>'text_field','label'=>'Email','type'=>'email','required'=>'required','value'=>$user_data['email']??'');
                    $config['username']=array('field_type'=>'text_field','label'=>'Username','required'=>'required','value'=>$user_data['username']??'');
                    $options=$this->base_model->get_form_options(array('table'=>'country','order'=>array('country'=>'ASC')));
                    $config['country']=array('field_type','checklist','label'=>'Country','required'=>'required','options'=>$options,'value'=>$user_data['country']??'');

                    $config['theme']=array('field_type'=>'select_field','label'=>'Theme','type'=>'select','option'=>get_themes(),'value'=>$user_data['theme']??'');
                    $config['comment']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$user_data['comment']??'');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit User';
                    $vars['submit_url']="/users/edit_user/{$user_data['id']}";
                    $vars['content_view']='form';
                    $vars['title']='Edit User';
                }
            } else
            {
                $vars['content_view']='unathourized';
                $vars['title']='Unathourized';
            }
            return view($vars('content_view',$vars));
        }
    }
    public function new_user($load_type='')
    {
        if($this->request->getPost('submit')) {
            unset($_POST['submit']);
            if(!user_has_access($this->controller)) {
                exit(json_encode(array('status'=>0,'msg'=>'Access Denied')));

            }
            $validation =\Config\Services::validation();
            $validation_rule=array('first_name'=>'required','last_name'=>'required','username'=>'required');
            $validation->setRules($validation_rule);
            if($validation->withRequest($this->request)->run()) {
                $existing_users=$this->base_model->get_data(array('table'=>'users','where'=>array('username'=>$this->request->getPost('username'))),assoc:true);
                if(!empty($existing_users))
                {
                    exit(json_encode(array('status'=>0,'msg'=>'Username already taken')));
                }
                $user_data=array();
                foreach ($_POST as $key=>$value)
                    if(empty($value))
                    {
                        unset($_POST[$key]);
                    }
                else if(is_array($value))
                {
                    $user_data[$key]=implode(',',$value);
                }
                else
                {
                    $user_data[$key]=$value;
                }
                $user_data['password']=password_hash($this->request->getPost('password'),PASSWORD_DEFAULT);
                $date=date('Y-m-d H:i:s');
                $time=date('H:i:s');
                $user_data['date_time_created']=$date.' '.$time;
                $id=$this->base_model->insert_data(array('table'=>'users','data'=>$user_data));
                exit(json_encode(array('status'=>1,'msg'=>'Account created successfully details saved')));
            }
            else
            {
                exit(json_encode(array('status'=>0,'msg'=>'Validation errors')));
            }
        }
        else
        {
            if(user_has_access($this->controller)) {
                $config=array();
                $config['first_name']=array('field_type'=>'text','label'=>'First Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$user_data['first_name']??'');
                $config['last_name']=array('field_type'=>'text','label'=>'Last Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$user_data['last_name']??'');
                $config['gender']=array('field_type'=>'select_field','label'=>'Gender','type'=>'select','required'=>'required','option'=>get_genders_array(),'value'=>$user_data['gender']??'');
                $config['phone_number']=array('field_type'=>'text_field','label'=>'Phone Number','type'=>'text','required'=>'required','value'=>$user_data['phone_number']??'');
                $config['email']=array('field_type'=>'text_field','label'=>'Email','type'=>'email','required'=>'required','value'=>$user_data['email']??'');
                $config['username']=array('field_type'=>'text_field','label'=>'Username','required'=>'required','value'=>$user_data['username']??'');
                $options=$this->base_model->get_form_options(array('table'=>'country','order'=>array('country'=>'ASC')));
                $config['country']=array('field_type','checklist','label'=>'Country','required'=>'required','options'=>$options,'value'=>$user_data['country']??'');

                $config['theme']=array('field_type'=>'select_field','label'=>'Theme','type'=>'select','option'=>get_themes(),'value'=>$user_data['theme']??'');
                $config['comment']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$user_data['comment']??'');
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New User';
                $vars['submit_url']="/users/new_user/{$user_data['id']}";
                $vars['content_view']='form';
                $vars['title']='New User';
            }
            else
            {
                $vars['content_view']='unathourized';
                $vars['title']='Unathourized';
            }
            return view($vars('content_view',$vars));
        }

    }
    public function  reset_password($id=0)
    {
        if($this->request->getPost('submit')) {
            unset($_POST['submit']);
            if(user_has_access($this->controller)) {
                $query_params=array('table'=>'users','where'=>array('id'=>$id));
                $user_data=$this->base_model->get_data($query_params);
                if(empty($user_data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='User Not Found';
                }
                else
                {
                    $config=array();
                    $config['password']=array('field_type');
                }
            }
        }
    }
}