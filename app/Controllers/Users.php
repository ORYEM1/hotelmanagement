<?php
namespace App\Controllers;
class Users extends BaseController
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
                    $config['first_name']=array('field_type'=>'text','label'=>'First Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','field_value'=>$user_data['first_name']??'');
                    $config['last_name']=array('field_type')
                }
            }
        }
    }
}