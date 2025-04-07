<?php

namespace App\Controllers;

class Whitelisted_user_ips extends RestrictedBaseController
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
            $table='whitelisted_user_ips';
            $_SESSION["search_{$table}"]=array();
            $_SESSION["search_{$table}_where_in"]=array();
            $_SESSION["search_{$table}_like"]=array();
            if($this->request->getPost('search'))
            {
                $params=array();
                $params['table_name']=$table;
                $params['where']=$where??array();
                $params['where_in']=$where_in??array();
                $params['where_search_fields']=array('id'=>'id');
                $params['where_in_search_fields']=array('name'=>'name');
                $search_range=array();
                $search_range['from_date_time']=array('column'=>'date_time_created','operator'=>'>=');
                $search_range['to_date_time']=array('column'=>'date_time_created','operator'=>'<=');
                $params['search_range']=$search_range;
                $this->set_search_data($params);
            }
            $vars['content_view']='data_table';
            $vars['title']='Whitelisted User IP Pools';
            $vars['page_heading']='Whitelisted User IP Pools';

            //Data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Pool Name','sortable'=>true,'db_col_name'=>'name');
            $data_header[]=array('name'=>'Date Added','sortable'=>true,'db_col_name'=>'date_time_created');
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            //============================================================================================
            $data_footer[]=get_advanced_search_button();
            $data_footer[]=get_new_link_button(array('url'=>"/whitelisted_user_ips/new_pool",'label'=>'New Pool','icon'=>'fa fa-plus'));
            $actions['Delete Selected']='delete_selected_ip_pools';
            $data_footer[]=get_actions_field(array('options'=>$actions));
            $vars['data_footer']=$data_footer;
            $vars['data_footer']=$data_footer;

            //Data tables options
            //============================================================================================
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_whitelisted_user_ips'),'bFilter'=>true,'order_columns'=>array('ID'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //Advance search options
            //============================================================================================
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Pool ID','name'=>'id','type'=>'number'));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Pool Name','name'=>'name','type'=>'text'));
            $advanced_search_fields[]=$this->advanced_search->get_date_time_range(array('label'=>'Date & Time Added'));
            $vars['advanced_search_fields']=$advanced_search_fields;
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view('page',$vars);
    }
    public function view_pool($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id', 'name', 'date_time_created', 'pool',  "CONCAT(users.first_name,' ',users.other_names) AS created_by");
            $join[]=array('table'=>'users','on'=>'users.id=whitelisted_user_ips.created_by','type'=>'left');
            $data=$this->base_model->get_data(array('table'=>'whitelisted_user_ips','join'=>$join,'fields'=>$fields,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='whitelisted_user_ips/view_pool';
                $vars['title']='User IP Pool';
                $vars['page_heading']='User IP Pool';
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_pool($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'whitelisted_user_ips','where'=>array('id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit a pool')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array('name' =>'required','pool' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'whitelisted_user_ips','where'=>array('name'=>$this->request->getPost('name'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The pool name {$this->request->getPost('name')} is already assigned to another pool")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The pool name {$this->request->getPost('name')} is already assigned to another pool")));
                }
                $_POST['pool']=str_replace(' ','',$_POST['pool']);
                $data=array();
                foreach($_POST as $key=>$value)
                {
                    if(strlen($value)==0)
                    {
                        $data[$key]=null;
                    }
                    else
                    {
                        $data[$key]=$value;
                    }
                }
                
                $this->base_model->update_data(array('table'=>'whitelisted_user_ips','where'=>array('id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Pool updated successfully")));
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
                $data=$this->base_model->get_data(array('table'=>'whitelisted_user_ips','where'=>array('id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['name']=array('field_type'=>'text_field','label'=>'pool Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$data['name']??'');
                    $config['pool']=array('field_type'=>'textarea','label'=>'IP Pool','type'=>'text','required'=>'required','value'=>$data['pool']??'','cols'=>300,'rows'=>3);
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Pool';
                    $vars['submit_url']= base_url("whitelisted_user_ips/edit_pool/{$data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Edit Pool';
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
    public function new_pool()
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission required to add a pool')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('name' =>'required','pool' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'whitelisted_user_ips','where'=>array('name'=>$this->request->getPost('name'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The pool name {$this->request->getPost('name')} is already assigned to another pool")));
                }
                $_POST['pool']=str_replace(' ','',$_POST['pool']);
                $data=array();
                foreach($_POST as $key=>$value)
                {
                    if(strlen($value)==0)
                    {
                        $data[$key]=null;
                    }
                    else
                    {
                        $data[$key]=$value;
                    }
                }
                $data['created_by']=$_SESSION['user_data']['id'];
                $data['date_time_created']=date('Y-m-d H:i:s');
                $id=$this->base_model->insert_data('whitelisted_user_ips',$data);
                exit(json_encode(array('status'=>1,'msg'=>"Pool added successfully. ID:{$id}")));
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
                $config['name']=array('field_type'=>'text_field','label'=>'pool Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['name']??'');
                $config['pool']=array('field_type'=>'textarea','label'=>'IP Pool','type'=>'text','required'=>'required','value'=>$_POST['pool']??'','cols'=>300,'rows'=>3);
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New Pool';
                $vars['submit_url']= base_url("whitelisted_user_ips/new_pool");
                $vars['content_view']='form';
                $vars['title']='New Pool';
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