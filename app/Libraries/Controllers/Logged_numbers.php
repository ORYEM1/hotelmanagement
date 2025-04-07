<?php

namespace App\Controllers;

class Logged_numbers extends RestrictedBaseController
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
            $table='logged_numbers';
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
                $params['like_search_fields']=array('number'=>'number');
                $this->set_search_data($params);
            }
            $vars['content_view']='data_table';
            $vars['title']='Logged Numbers';
            $vars['page_heading']='Logged Numbers';

            //Data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Date & Time Created','sortable'=>false);
            $data_header[]=array('name'=>'Number','sortable'=>false);
            $data_header[]=array('name'=>'Wallet Name','sortable'=>true,'db_col_name'=>'wallets.wallet_name');
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            //============================================================================================
            $data_footer[]=get_advanced_search_button();
            $data_footer[]=get_new_link_button(array('url'=>"/logged_numbers/new_logged_number",'label'=>'Add Number','icon'=>'fa fa-plus'));
            $actions['Delete Selected']='delete_selected_logged_numbers';
            $data_footer[]=get_actions_field(array('options'=>$actions));
            $vars['data_footer']=$data_footer;

            //Data tables options
            //============================================================================================
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_logged_numbers'),'bFilter'=>true,'order_columns'=>array('id'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //Advance search options
            //============================================================================================
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Logged Number ID','name'=>'id','type'=>'number'));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Phone Number','name'=>'number','type'=>'number'));
            $vars['advanced_search_fields']=$advanced_search_fields;
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view('page',$vars);
    }
    public function view_logged_number($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id', 'date_time_created', 'number', 'wallet_id', 'created_by', 'wallets.wallet_name AS wallet_name', "CONCAT(users.first_name,' ',users.other_names) AS created_by");
            $join[]=array('table'=>'users','on'=>'users.id=logged_numbers.created_by','type'=>'left');
            $join[]=array('table'=>'wallets','on'=>'wallets.id=logged_numbers.wallet_id','type'=>'left');
            $data=$this->base_model->get_data(array('table'=>'logged_numbers','fields'=>$fields,'join'=>$join,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['content_view']='logged_numbers/view_logged_number';
                $vars['title']='Logged Number';
                $vars['page_heading']='Logged Number';
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_logged_number($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'logged_numbers','where'=>array('id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit logged number')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array('number' =>'required','wallet_id'=>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
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
                
                $this->base_model->update_data(array('table'=>'logged_numbers','where'=>array('id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Logged Number updated successfully")));
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
                $data=$this->base_model->get_data(array('table'=>'logged_numbers','where'=>array('id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['number']=array('field_type'=>'text_field','label'=>'Number','type'=>'number','autofocus'=>'autofocus','required'=>'required','value'=>$data['number']??'');
                    $options=$this->base_model->get_form_options(array('table'=>'wallets','fields'=>array('id','wallet_name'),'order'=>'wallet_name'),'id','wallet_name');
                    $config['wallet_id']=array('field_type'=>'select_field','label'=>'Wallet','options'=>$options,'required'=>'required','value'=>$data['wallet_id']??'');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Logged Number';
                    $vars['submit_url']= base_url("logged_numbers/edit_logged_number/{$data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Edit Logged Number';
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
    public function new_logged_number()
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            unset($_POST['wallet_name']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission required to add a new logged number')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('number' =>'required','wallet_id' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
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
                $id=$this->base_model->insert_data('logged_numbers',$data);
                exit(json_encode(array('status'=>1,'msg'=>"Logged Number created successfully. ID:{$id}")));
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
                $config['number']=array('field_type'=>'text_field','label'=>'Number','type'=>'number','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['number']??'');
                $options=$this->base_model->get_form_options(array('table'=>'wallets','fields'=>array('id','wallet_name'),'order'=>'wallet_name'),'id','wallet_name');
                $config['wallet_id']=array('field_type'=>'select_field','label'=>'Wallet','options'=>$options,'required'=>'required','value'=>$_POST['wallet_id']??'');
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New Logged Number';
                $vars['submit_url']= base_url("logged_numbers/new_logged_number");
                $vars['content_view']='form';
                $vars['title']='New Logged Number';
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