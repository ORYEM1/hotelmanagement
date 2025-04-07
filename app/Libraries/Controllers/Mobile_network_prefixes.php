<?php

namespace App\Controllers;

class Mobile_network_prefixes extends RestrictedBaseController
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
            $table='mobile_network_prefixes';
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
                $params['where_in_search_fields']=array('prefix'=>'prefix','wallet_id'=>'wallet_id');
                $search_range=array();
                $search_range['from_date_time']=array('column'=>'date_time_created','operator'=>'>=');
                $search_range['to_date_time']=array('column'=>'date_time_created','operator'=>'<=');
                $params['search_range']=$search_range;
                $this->set_search_data($params);
            }
            $vars['content_view']='data_table';
            $vars['title']='Mobile Network Prefixes';
            $vars['page_heading']='Mobile Network Prefixes';

            //Data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Prefix','sortable'=>true,'db_col_name'=>'prefix');
            $data_header[]=array('name'=>'Wallet','sortable'=>true,'db_col_name'=>'wallets.wallet_name');
            $data_header[]=array('name'=>'Date Added','sortable'=>true,'db_col_name'=>'date_time_created');
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            //============================================================================================
            $data_footer[]=get_advanced_search_button();
            $data_footer[]=get_new_link_button(array('url'=>"/mobile_network_prefixes/new_prefix",'label'=>'New Prefix','icon'=>'fa fa-plus'));
            $actions['Delete Selected']='delete_selected_prefixes';
            $data_footer[]=get_actions_field(array('options'=>$actions));
            $vars['data_footer']=$data_footer;
            $vars['data_footer']=$data_footer;

            //Data tables options
            //============================================================================================
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_network_prefixes'),'bFilter'=>true,'order_columns'=>array('ID'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //Advance search options
            //============================================================================================
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Prefix ID','name'=>'id','type'=>'number'));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Prefix','name'=>'prefix','type'=>'number'));
            $options=$this->base_model->get_form_options(array('table'=>'wallets','fields'=>array('id','wallet_name'),'order'=>'wallet_name'),'id','wallet_name');
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Wallet','name'=>'wallet_id','options'=>$options));
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
    public function view_prefix($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id', 'prefix', 'date_time_created', 'wallets.wallet_name',  "CONCAT(users.first_name,' ',users.other_names) AS created_by");
            $join[]=array('table'=>'users','on'=>'users.id=mobile_network_prefixes.user_id','type'=>'left');
            $join[]=array('table'=>'wallets','on'=>'wallets.id=mobile_network_prefixes.wallet_id','type'=>'left');
            $data=$this->base_model->get_data(array('table'=>'mobile_network_prefixes','join'=>$join,'fields'=>$fields,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='mobile_network_prefixes/view_prefix';
                $vars['title']='Network Prefix';
                $vars['page_heading']='Network Prefix';
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_prefix($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'mobile_network_prefixes','where'=>array('id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit prefix')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array('prefix' =>'required','wallet_id' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'mobile_network_prefixes','where'=>array('prefix'=>$this->request->getPost('prefix'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The prefix {$this->request->getPost('prefix')} is already in the database")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The prefix {$this->request->getPost('prefix')} is already in the database")));
                }
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
                
                $this->base_model->update_data(array('table'=>'mobile_network_prefixes','where'=>array('id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Prefix updated successfully")));
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
                $data=$this->base_model->get_data(array('table'=>'mobile_network_prefixes','where'=>array('id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['prefix']=array('field_type'=>'text_field','label'=>'Prefix`','type'=>'number','autofocus'=>'autofocus','required'=>'required','value'=>$data['prefix']??'');
                    $options=$this->base_model->get_form_options(array('table'=>'wallets','fields'=>array('id','wallet_name'),'order'=>'wallet_name'),'id','wallet_name');
                    $config['wallet_id']=array('field_type'=>'select_field','label'=>'Wallet','options'=>$options,'required'=>'required','value'=>$data['wallet_id']??'');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Prefix';
                    $vars['submit_url']= base_url("mobile_network_prefixes/edit_prefix/{$data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Edit Prefix';
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
    public function new_prefix()
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission required to add a prefix')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('prefix' =>'required','wallet_id' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'mobile_network_prefixes','where'=>array('prefix'=>$this->request->getPost('prefix'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The prefix {$this->request->getPost('prefix')} is already in the database")));
                }
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
                $data['user_id']=$_SESSION['user_data']['id'];
                $id=$this->base_model->insert_data('mobile_network_prefixes',$data);
                exit(json_encode(array('status'=>1,'msg'=>"Prefix added successfully. ID:{$id}")));
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
                $config['prefix']=array('field_type'=>'text_field','label'=>'Prefix','type'=>'number','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['prefix']??'');
                $options=$this->base_model->get_form_options(array('table'=>'wallets','fields'=>array('id','wallet_name'),'order'=>'wallet_name'),'id','wallet_name');
                $config['wallet_id']=array('field_type'=>'select_field','label'=>'Wallet','options'=>$options,'required'=>'required','value'=>$_POST['wallet_id']??'');
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New Prefix';
                $vars['submit_url']= base_url("mobile_network_prefixes/new_prefix");
                $vars['content_view']='form';
                $vars['title']='New Prefix';
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