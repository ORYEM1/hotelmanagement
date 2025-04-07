<?php

namespace App\Controllers;

class Supported_currencies extends RestrictedBaseController
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
            $table='supported_currencies';
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
                $params['where_in_search_fields']=array('code'=>'code');
                $this->set_search_data($params);
            }
            $vars['content_view']='data_table';
            $vars['title']='Supported Currencies';
            $vars['page_heading']='Supported Currencies';

            //Data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Code','sortable'=>true,'db_col_name'=>'code');
            $data_header[]=array('name'=>'Name','sortable'=>true,'db_col_name'=>'name');
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            //============================================================================================
            $data_footer[]=get_advanced_search_button();
            $data_footer[]=get_new_link_button(array('url'=>"/supported_currencies/new_currency",'label'=>'New Currency','icon'=>'fa fa-plus'));
            $actions['Delete Selected']='delete_selected_currencies';
            $data_footer[]=get_actions_field(array('options'=>$actions));
            $vars['data_footer']=$data_footer;
            $vars['data_footer']=$data_footer;

            //Data tables options
            //============================================================================================
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_supported_currencies'),'bFilter'=>true,'order_columns'=>array('Name'=>'asc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //Advance search options
            //============================================================================================
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Country ID','name'=>'id','type'=>'number'));
            $options=$this->base_model->get_form_options(array('table'=>'supported_currencies','fields'=>array('code'),'order'=>'code'),'code','code');
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Currency','name'=>'code','options'=>$options));
            $vars['advanced_search_fields']=$advanced_search_fields;
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view('page',$vars);
    }
    public function view_currency($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id', 'code', 'name',  "CONCAT(users.first_name,' ',users.other_names) AS created_by");
            $join[]=array('table'=>'users','on'=>'users.id=supported_currencies.created_by','type'=>'left');
            $data=$this->base_model->get_data(array('table'=>'supported_currencies','join'=>$join,'fields'=>$fields,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='supported_currencies/view_currency';
                $vars['title']='Currency';
                $vars['page_heading']='Currency';
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_currency($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'supported_currencies','where'=>array('id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit currency')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array('code' =>'required','name' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'supported_currencies','where'=>array('code'=>$this->request->getPost('code'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The currency code {$this->request->getPost('code')} is already assigned to another currency")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The currency code  {$this->request->getPost('code')} is already assigned to another currency")));
                }
                $existing_data=$this->base_model->get_data(array('table'=>'supported_currencies','where'=>array('name'=>$this->request->getPost('name'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The currency name {$this->request->getPost('name')} is already assigned to another currency")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The currency name  {$this->request->getPost('name')} is already assigned to another currency")));
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
                
                $this->base_model->update_data(array('table'=>'supported_currencies','where'=>array('id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Currency updated successfully")));
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
                $data=$this->base_model->get_data(array('table'=>'supported_currencies','where'=>array('id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['code']=array('field_type'=>'text_field','label'=>'Country Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$data['code']??'');
                    $config['name']=array('field_type'=>'text_field','label'=>'Nationality','type'=>'text','required'=>'required','value'=>$data['name']??'');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Currency';
                    $vars['submit_url']= base_url("supported_currencies/edit_currency/{$data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Edit Currency';
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
    public function new_currency()
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            unset($_POST['base_role']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission required to add a currency')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('code' =>'required','name' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'supported_currencies','where'=>array('code'=>$this->request->getPost('code'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The currency code {$this->request->getPost('code')} is already assigned to another currency")));
                }
                $existing_data=$this->base_model->get_data(array('table'=>'supported_currencies','where'=>array('name'=>$this->request->getPost('name'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The currency name {$this->request->getPost('name')} is already assigned to another currency")));
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
                $data['created_by']=$_SESSION['user_data']['id'];
                $id=$this->base_model->insert_data('supported_currencies',$data);
                exit(json_encode(array('status'=>1,'msg'=>"Currency added successfully. ID:{$id}")));
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
                $config['code']=array('field_type'=>'text_field','label'=>'Currency Code','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['code']??'');
                $config['name']=array('field_type'=>'text_field','label'=>'Currency Name','type'=>'text','required'=>'required','value'=>$_POST['name']??'');
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New Currency';
                $vars['submit_url']= base_url("supported_currencies/new_currency");
                $vars['content_view']='form';
                $vars['title']='New Currency';
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