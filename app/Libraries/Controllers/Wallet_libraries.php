<?php

namespace App\Controllers;

class Wallet_libraries extends RestrictedBaseController
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
            $table='wallet_libraries';
            $_SESSION["search_{$table}"]=array();
            $_SESSION["search_{$table}_where_in"]=array();
            $_SESSION["search_{$table}_like"]=array();
            if($this->request->getPost('search'))
            {
                $params=array();
                $params['table_name']=$table;
                $params['where']=$where??array();
                $params['where_in']=$where_in??array();
                $params['where_search_fields']=array('id'=>'id','country_id'=>'country_id','wallet_id'=>'wallet_id','library'=>'library');
                $params['where_in_search_fields']=array('status'=>'status','log_requests'=>'log_requests','use_gateway'=>'use_gateway');
                $search_range=array();
                $search_range['from_date_time']=array('column'=>'date_time_created','operator'=>'>=');
                $search_range['to_date_time']=array('column'=>'date_time_created','operator'=>'<=');
                $params['search_range']=$search_range;
                $this->set_search_data($params);
            }
            $vars['content_view']='data_table';
            $vars['title']='Wallet Libraries';
            $vars['page_heading']='Wallet Libraries';

            //Data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Library','sortable'=>true,'db_col_name'=>'readable_name');
            $data_header[]=array('name'=>'Wallet','sortable'=>true,'db_col_name'=>'wallets.wallet_name');
            $data_header[]=array('name'=>'Country','sortable'=>true,'db_col_name'=>'countries.country');
            $data_header[]=array('name'=>'Wallet Status','sortable'=>false);
            $data_header[]=array('name'=>'Logging','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            //============================================================================================
            $data_footer[]=get_advanced_search_button();
            $data_footer[]=get_new_link_button(array('url'=>"/wallet_libraries/new_library",'label'=>'Add Library','icon'=>'fa fa-plus'));
            $actions['Activate Library']='activate_selected_wallet_libraries';
            $actions['Deactivate Library']='deactivate_selected_wallet_libraries';
            $actions['Activate Logging']='activate_logging_for_selected_wallet_libraries';
            $actions['Deactivate Logging']='deactivate_logging_for_selected_wallet_libraries';
            $data_footer[]=get_actions_field(array('options'=>$actions));
            $vars['data_footer']=$data_footer;

            //Data tables options
            //============================================================================================
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_wallet_libraries'),'bFilter'=>true,'order_columns'=>array('ID'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //Advance search options
            //============================================================================================
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Library ID','name'=>'id','type'=>'number'));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Library','name'=>'library','type'=>'text'));
            $options=$this->base_model->get_form_options(array('table'=>'countries','fields'=>array('id','country'),'order'=>'country','where'=>array('supported'=>'1')),'id','country');
            $advanced_search_fields[]=$this->advanced_search->get_select_search(array('label'=>'Country','name'=>'country_id','class'=>'linked_input select','data-linked_id'=>'wallet_id','options'=>$options));
            $options=$this->base_model->get_form_options(array('table'=>'wallets','fields'=>array('id','wallet_name'),'order'=>'wallet_name'),'id','wallet_name');
            $advanced_search_fields[]=$this->advanced_search->get_select_search(array('label'=>'Wallet','name'=>'wallet_id','data-source'=>base_url('rpc/get_wallet_options'),'id'=>'wallet_id','options'=>$options));
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Library Status','name'=>'status','options'=>get_statuses_array()));
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Logging','name'=>'log_requests','options'=>get_statuses_array()));
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Use a Gateway','name'=>'use_gateway','options'=>get_statuses_array()));
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
    public function view_library($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id', 'date_time_created', 'library', 'readable_name', 'status', 'transaction_types', 'log_requests', 'log_type', 'use_gateway', 'gateway_url', 'gateway_functions', 'comment', 'countries.country','wallets.wallet_name',  "CONCAT(users.first_name,' ',users.other_names) AS created_by");
            $join[]=array('table'=>'users','on'=>'users.id=wallet_libraries.created_by','type'=>'left');
            $join[]=array('table'=>'wallets','on'=>'wallets.id=wallet_libraries.wallet_id','type'=>'left');
            $join[]=array('table'=>'countries','on'=>'countries.id=wallet_libraries.country_id','type'=>'left');
            $data=$this->base_model->get_data(array('table'=>'wallet_libraries','join'=>$join,'fields'=>$fields,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['gateway_functions']=array();
                if(!file_exists(APPPATH.'Libraries/'.$data['library'].'.php'))
                {
                    $vars['library_exists']=false;
                }
                else
                {
                    $vars['library_exists']=true;
                    $vars['gateway_functions']=get_gateway_functions($data['library']);
                }

                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='wallet_libraries/view_library';
                $vars['title']='Wallet Library';
                $vars['page_heading']='Wallet Library';
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_library($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'wallet_libraries','where'=>array('id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit library')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array('library' =>'required','readable_name' =>'required','status' =>'required','log_requests' =>'required','log_type' =>'required','use_gateway' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'wallet_libraries','where'=>array('library'=>$this->request->getPost('library'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The library {$this->request->getPost('library')} is already in the database")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The library {$this->request->getPost('library')} is already in the database")));
                }

                $existing_data=$this->base_model->get_data(array('table'=>'wallet_libraries','where'=>array('readable_name'=>$this->request->getPost('readable_name'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The library name {$this->request->getPost('readable_name')} is already assigned to another library")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The library name {$this->request->getPost('readable_name')} is already assigned to another library")));
                }

                $data=array();
                foreach($_POST as $key=>$value)
                {
                    if(empty($value)&&$value!=0)
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
                
                $this->base_model->update_data(array('table'=>'wallet_libraries','where'=>array('id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Library updated successfully")));
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
                $data=$this->base_model->get_data(array('table'=>'wallet_libraries','where'=>array('id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['library']=array('field_type'=>'select_field','options'=>get_wallet_libraries(),'data-linked_id'=>'gw_functions','class'=>'text linked_checklist','label'=>'Library','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$data['library']??'');
                    $config['readable_name']=array('field_type'=>'text_field','label'=>'Library Name','type'=>'text','required'=>'required','value'=>$data['readable_name']??'');
                    $options=$this->base_model->get_form_options(array('table'=>'countries','fields'=>array('id','country'),'order'=>'country','where'=>array('supported'=>1)),'id','country');
                    $config['country_id']=array('field_type'=>'select_field','data-linked_id'=>'wallet_id2','class'=>'select linked_input','label'=>'Country','options'=>$options,'value'=>$data['country_id']??'');
                    $options=$this->base_model->get_form_options(array('table'=>'wallets','fields'=>array('id','wallet_name'),'order'=>'wallet_name'),'id','wallet_name');
                    $config['wallet_id']=array('field_type'=>'select_field','id'=>'wallet_id2','data-source'=>'/rpc/get_wallet_options','label'=>'Wallet','options'=>$options,'value'=>$data['wallet_id']??'');
                    $config['status']=array('field_type'=>'select_field','label'=>'Wallet Status','required'=>'required','options'=>get_statuses_array(),'value'=>$data['status']??'');
                    $config['transaction_types']=array('field_type'=>'checklist','label'=>'Possible Transactions','options'=>get_transaction_types(),'value'=>$data['transaction_types']??'');
                    $config['use_gateway']=array('field_type'=>'select_field','label'=>'Use Gateway','required'=>'required','options'=>get_statuses_array(),'value'=>$data['use_gateway']??'');
                    $config['gateway_url']=array('field_type'=>'text_field','label'=>'Gateway URL','type'=>'url','value'=>$data['gateway_url']??'');
                    $config['log_requests']=array('field_type'=>'select_field','label'=>'Logging','required'=>'required','options'=>get_statuses_array(),'value'=>$data['log_requests']??'');
                    $config['log_type']=array('field_type'=>'select_field','label'=>'Log Type','required'=>'required','options'=>get_log_types(),'value'=>$data['log_type']??'');
                    $config['gateway_functions']=array('field_type'=>'checklist','attributes'=>array('id'=>'gw_functions','data-name'=>'gateway_functions','data-source'=>'/rpc/get_gateway_functions_checklist','data-initial_value'=>$data['gateway_functions']),'label'=>'Gateway Functions','options'=>get_gateway_functions($data['library']),'value'=>$data['gateway_functions']??'');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Library';
                    $vars['submit_url']= "/wallet_libraries/edit_library/{$data['id']}";
                    $vars['content_view']='form';
                    $vars['title']='Edit Library';
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
    public function new_library()
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission required to add a library')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('library' =>'required','readable_name' =>'required','status' =>'required','log_requests' =>'required','log_type' =>'required','use_gateway' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'wallet_libraries','where'=>array('library'=>$this->request->getPost('library'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The library {$this->request->getPost('library')} is already in the database")));
                }
                $existing_data=$this->base_model->get_data(array('table'=>'wallet_libraries','where'=>array('readable_name'=>$this->request->getPost('readable_name'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The library name {$this->request->getPost('readable_name')} is already in the database")));
                }
                $data=array();
                foreach($_POST as $key=>$value)
                {
                    if(empty($value)&&$value!=0)
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
                $data['created_by']=$_SESSION['user_data']['id'];
                $data['date_time_created']=date('Y-m-d H:i:s');
                $id=$this->base_model->insert_data('wallet_libraries',$data);
                exit(json_encode(array('status'=>1,'msg'=>"Library added successfully. ID:{$id}")));
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
                $config['library']=array('field_type'=>'select_field','options'=>get_wallet_libraries(),'data-linked_id'=>'gw_functions','class'=>'text linked_checklist','label'=>'Library','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['library']??'');
                $config['readable_name']=array('field_type'=>'text_field','label'=>'Library Name','type'=>'text','required'=>'required','value'=>$_POST['readable_name']??'');
                $options=$this->base_model->get_form_options(array('table'=>'countries','fields'=>array('id','country'),'order'=>'country','where'=>array('supported'=>1)),'id','country');
                $config['country_id']=array('field_type'=>'select_field','data-linked_id'=>'wallet_id2','class'=>'select linked_input','label'=>'Country','options'=>$options,'value'=>$_POST['country_id']??'');
                $options=$this->base_model->get_form_options(array('table'=>'wallets','fields'=>array('id','wallet_name'),'order'=>'wallet_name'),'id','wallet_name');
                $config['wallet_id']=array('field_type'=>'select_field','id'=>'wallet_id2','data-source'=>'/rpc/get_wallet_options','label'=>'Wallet','options'=>$options,'value'=>$_POST['wallet_id']??'');
                $config['status']=array('field_type'=>'select_field','label'=>'Wallet Status','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['status']??'');
                $config['transaction_types']=array('field_type'=>'checklist','label'=>'Possible Transactions','options'=>get_transaction_types(),'value'=>$_POST['transaction_types']??'');
                $config['use_gateway']=array('field_type'=>'select_field','label'=>'Use Gateway','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['use_gateway']??'');
                $config['gateway_url']=array('field_type'=>'text_field','label'=>'Gateway URL','type'=>'url','value'=>$_POST['gateway_url']??'');
                $config['log_requests']=array('field_type'=>'select_field','label'=>'Logging','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['log_requests']??'');
                $config['log_type']=array('field_type'=>'select_field','label'=>'Log Type','required'=>'required','options'=>get_log_types(),'value'=>$_POST['log_type']??'');
                $config['gateway_functions']=array('field_type'=>'checklist','attributes'=>array('id'=>'gw_functions','data-name'=>'gateway_functions','data-source'=>'/rpc/get_gateway_functions_checklist'),'label'=>'Gateway Functions','options'=>array(),'value'=>$_POST['gateway_functions']??'');
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New Library';
                $vars['submit_url']= base_url("wallet_libraries/new_library");
                $vars['content_view']='form';
                $vars['title']='New Library';
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