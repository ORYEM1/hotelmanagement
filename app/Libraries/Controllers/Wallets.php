<?php

namespace App\Controllers;

class Wallets extends RestrictedBaseController
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
            $table='wallets';
            $_SESSION["search_{$table}"]=array();
            $_SESSION["search_{$table}_where_in"]=array();
            $_SESSION["search_{$table}_like"]=array();
            if($this->request->getPost('search'))
            {
                $params=array();
                $params['table_name']=$table;
                $params['where']=$where??array();
                $params['where_in']=$where_in??array();
                $params['where_search_fields']=array('id'=>'id','code'=>'code');
                $params['where_in_search_fields']=array('status'=>'status','currency'=>'currency','country_id'=>'country_id');
                $this->set_search_data($params);
            }

            $vars['content_view']='data_table';
            $vars['title']='Wallets';
            $vars['page_heading']='Wallets';

            //Data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Wallet Code','sortable'=>true,'db_col_name'=>'code');
            $data_header[]=array('name'=>'Collection API','sortable'=>false);
            $data_header[]=array('name'=>'Disbursement API','sortable'=>false);
            $data_header[]=array('name'=>'Wallet Status','sortable'=>false);
            $data_header[]=array('name'=>'Collection Status','sortable'=>false);
            $data_header[]=array('name'=>'Disbursement Status','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            //============================================================================================
            $data_footer[]=get_advanced_search_button();
            $data_footer[]=get_new_link_button(array('url'=>"/wallets/new_wallet",'label'=>'New Wallet','icon'=>'fa fa-plus'));
            $actions=array();
            $actions['Activate Wallets']='activate_selected_wallets';
            $actions['Deactivate Wallets']='deactivate_selected_wallets';
            $actions['Activate Collection']='activate_collection_for_selected_wallets';
            $actions['Deactivate Collection']='deactivate_collection_for_selected_wallets';
            $actions['Activate Disbursement']='activate_disbursement_for_selected_wallets';
            $actions['Deactivate Disbursement']='deactivate_disbursement_for_selected_wallets';
            $data_footer[]=get_actions_field(array('options'=>$actions));
            $vars['data_footer']=$data_footer;

            //Data tables options
            //============================================================================================
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_wallets'),'bFilter'=>true,'order_columns'=>array('ID'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //Advance search options
            //============================================================================================
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Wallet ID','name'=>'id','type'=>'number'));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Wallet Code','name'=>'code','type'=>'text'));
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Status','name'=>'status','options'=>get_statuses_array()));
            $options=$this->base_model->get_form_options(array('table'=>'supported_currencies','fields'=>array('code'),'order'=>'code'),'code','code');
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Currency','name'=>'currency','options'=>$options));
            $options=$this->base_model->get_form_options(array('table'=>'countries','fields'=>array('id','country'),'where'=>array('supported'=>1),'order'=>'country'),'id','country');
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Country','name'=>'country_id','options'=>$options));
            $vars['advanced_search_fields']=$advanced_search_fields;
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view('page',$vars);
    }
    public function view_wallet($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array( 'id', 'date_time_created', 'user_id', 'wallet_name', 'code', 'country_id', 'currency', 'status', 'collection_status', 'disbursement_status', 'min_collection', 'max_collection', 'min_disbursement', 'max_disbursement', 'collection_api', 'disbursement_api', 'collection_lib.readable_name AS collection_api_name', 'disbursement_lib.readable_name AS disbursement_api_name','countries.country', 'comment',"CONCAT(users.first_name,' ',users.other_names) AS created_by");
            $join[]=array('table'=>'users','on'=>'users.id=wallets.user_id','type'=>'left');
            $join[]=array('table'=>'countries','condition'=>'countries.id=wallets.country_id','type'=>'left');
            $join[]=array('table'=>'wallet_libraries collection_lib','condition'=>'collection_lib.id=wallets.collection_api','type'=>'left');
            $join[]=array('table'=>'wallet_libraries disbursement_lib','condition'=>'disbursement_lib.id=wallets.disbursement_api','type'=>'left');
            $data=$this->base_model->get_data(array('table'=>'wallets','join'=>$join,'fields'=>$fields,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='wallets/view_wallet';
                $vars['title']='Wallet Details';
                $vars['page_heading']='Wallet Details';
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_wallet($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'wallets','where'=>array('id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit wallet')));
            }

            $validation = \Config\Services::validation();
            $numbers=array('min_collection','max_collection','min_disbursement','max_disbursement');
            foreach ($numbers as $key)
            {
                if(!empty($_POST[$key]))
                {
                    $_POST[$key]=str_replace(',','',$_POST[$key]);
                    if(!is_numeric($_POST[$key]))
                    {
                        exit(json_encode(array('status'=>0,'msg'=>"{$key} must be a valid number")));
                    }
                }
            }
            $validation_rules=array('wallet_name' =>'required','code' =>'required','currency'=>'required', 'status'=>'required','collection_status'=>'required','disbursement_status'=>'required','min_collection'=>'required','max_collection'=>'required','min_disbursement'=>'required','max_disbursement'=>'required','collection_api'=>'integer','disbursement_api'=>'integer');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'wallets','where'=>array('wallet_name'=>$this->request->getPost('wallet_name'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The wallet name {$this->request->getPost('wallet_name')} is already assigned to another wallet.")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The wallet name {$this->request->getPost('wallet_name')} is already assigned to another wallet")));
                }

                $existing_data=$this->base_model->get_data(array('table'=>'wallets','where'=>array('code'=>$this->request->getPost('code'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The wallet code {$this->request->getPost('code')} is already assigned to another wallet.")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The wallet code {$this->request->getPost('code')} is already assigned to another wallet")));
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
                
                $this->base_model->update_data(array('table'=>'wallets','where'=>array('id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Wallet updated successfully")));
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
                $data=$this->base_model->get_data(array('table'=>'wallets','where'=>array('id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['wallet_name']=array('field_type'=>'text_field','label'=>'Wallet Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$data['wallet_name']??'');
                    $config['code']=array('field_type'=>'text_field','label'=>'Wallet Code','type'=>'text','required'=>'required','value'=>$data['code']??'');
                    $options=$this->base_model->get_form_options(array('table'=>'countries','where'=>array('supported'=>1),'fields'=>array('id','country'),'order'=>'country'),'id','country');
                    $config['country_id']=array('field_type'=>'select_field','label'=>'Country','options'=>$options,'value'=>$data['country_id']??'');
                    $options=$this->base_model->get_form_options(array('table'=>'supported_currencies','fields'=>array('code'),'order'=>'code'),'code','code');
                    $config['currency']=array('field_type'=>'select_field','label'=>'Currency','options'=>$options,'required'=>'required','value'=>$data['currency']??'');
                    $config['status']=array('field_type'=>'select_field','label'=>'Wallet Status','required'=>'required','options'=>get_statuses_array(),'value'=>$data['status']??'');
                    $config['collection_status']=array('field_type'=>'select_field','label'=>'Collection Status','required'=>'required','options'=>get_statuses_array(),'value'=>$data['collection_status']??'');
                    $config['disbursement_status']=array('field_type'=>'select_field','label'=>'Disbursement Status','required'=>'required','options'=>get_statuses_array(),'value'=>$data['disbursement_status']??'');
                    $config['min_collection']=array('field_type'=>'text_field','label'=>'Min. Collection','type'=>'text','class'=>'text number','required'=>'required','value'=>isset($data['min_collection'])? number_format($data['min_collection']):'');
                    $config['max_collection']=array('field_type'=>'text_field','label'=>'Max. Collection','type'=>'text','class'=>' text number','required'=>'required','value'=>isset($data['max_collection'])? number_format($data['max_collection']):'');
                    $config['min_disbursement']=array('field_type'=>'text_field','label'=>'Min. Disbursement','type'=>'text','class'=>'text number','required'=>'required','value'=>isset($data['min_disbursement'])?number_format($data['min_disbursement']):'');
                    $config['max_disbursement']=array('field_type'=>'text_field','label'=>'Max. Disbursement','type'=>'text','class'=>'text number','required'=>'required','value'=>isset($data['max_disbursement'])?number_format($data['max_disbursement']):'');

                    $where='';
                    if(!empty($data['country_id']))
                    {
                        $where.=" (country_id='{$data['country_id']}' OR country_id IS NULL) ";
                    }
                    if(!empty($where))
                    {
                        $where.=" AND ";
                    }
                    $where.=" (wallet_id='{$data['id']}' OR wallet_id IS NULL) ";
                    $where_string=$where." AND transaction_types LIKE '%collection%' ";
                    $options=$this->base_model->get_form_options(array('table'=>'wallet_libraries','fields'=>array('id','readable_name'),'where_string'=>$where_string,'order'=>'readable_name'),'id','readable_name');
                    $config['collection_api']=array('field_type'=>'select_field','label'=>'Collection API','options'=>$options,'value'=>$data['collection_api']??'');

                    $where_string=$where." AND transaction_types LIKE '%disbursement%' ";
                    $options=$this->base_model->get_form_options(array('table'=>'wallet_libraries','fields'=>array('id','readable_name'),'where_string'=>$where_string,'order'=>'readable_name'),'id','readable_name');
                    $config['disbursement_api']=array('field_type'=>'select_field','label'=>'Disbursement API','options'=>$options,'value'=>$data['disbursement_api']??'');

                    $config['comment']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$data['comment']??'','cols'=>300,'rows'=>3);
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Wallet';
                    $vars['submit_url']= base_url("wallets/edit_wallet/{$data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Edit Wallet';
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
    public function new_wallet()
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            unset($_POST['base_role']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission required to add a wallet')));
            }
            $validation = \Config\Services::validation();
            $numbers=array('min_collection','max_collection','min_disbursement','max_disbursement');
            foreach ($numbers as $key)
            {
                if(!empty($_POST[$key]))
                {
                    $_POST[$key]=str_replace(',','',$_POST[$key]);
                    if(!is_numeric($_POST[$key]))
                    {
                        exit(json_encode(array('status'=>0,'msg'=>"{$key} must be a valid number")));
                    }
                }
            }
            $validation_rules=array('wallet_name' =>'required','code' =>'required','currency'=>'required', 'status'=>'required','collection_status'=>'required','disbursement_status'=>'required','min_collection'=>'required','max_collection'=>'required','min_disbursement'=>'required','max_disbursement'=>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'wallets','where'=>array('wallet_name'=>$this->request->getPost('wallet_name'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The wallet name {$this->request->getPost('wallet_name')} is already assigned to another wallet")));
                }
                $existing_data=$this->base_model->get_data(array('table'=>'wallets','where'=>array('code'=>$this->request->getPost('code'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The wallet code {$this->request->getPost('code')} is already assigned to another wallet")));
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
                $data['date_time_created']=date('Y-m-d H:i:s');
                $id=$this->base_model->insert_data('wallets',$data);
                exit(json_encode(array('status'=>1,'msg'=>"Wallet added successfully. ID:{$id}")));
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
                $config['wallet_name']=array('field_type'=>'text_field','label'=>'Wallet Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['wallet_name']??'');
                $config['code']=array('field_type'=>'text_field','label'=>'Wallet Code','type'=>'text','required'=>'required','value'=>$_POST['code']??'');
                $options=$this->base_model->get_form_options(array('table'=>'countries','where'=>array('supported'=>1),'fields'=>array('id','country'),'order'=>'country'),'id','country');
                $config['country_id']=array('field_type'=>'select_field','label'=>'Country','options'=>$options,'value'=>$_POST['country_id']??'');
                $options=$this->base_model->get_form_options(array('table'=>'supported_currencies','fields'=>array('code'),'order'=>'code'),'code','code');
                $config['currency']=array('field_type'=>'select_field','label'=>'Currency','options'=>$options,'required'=>'required','value'=>$_POST['currency']??'');
                $config['status']=array('field_type'=>'select_field','label'=>'Wallet Status','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['status']??'');
                $config['collection_status']=array('field_type'=>'select_field','label'=>'Collection Status','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['collection_status']??'');
                $config['disbursement_status']=array('field_type'=>'select_field','label'=>'Disbursement Status','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['disbursement_status']??'');
                $config['min_collection']=array('field_type'=>'text_field','label'=>'Min. Collection','type'=>'text','class'=>'text number','required'=>'required','value'=>isset($_POST['min_collection'])? number_format($_POST['min_collection']):'');
                $config['max_collection']=array('field_type'=>'text_field','label'=>'Max. Collection','type'=>'text','class'=>' text number','required'=>'required','value'=>isset($_POST['max_collection'])? number_format($_POST['max_collection']):'');
                $config['min_disbursement']=array('field_type'=>'text_field','label'=>'Min. Disbursement','type'=>'text','class'=>'text number','required'=>'required','value'=>isset($_POST['min_disbursement'])?number_format($_POST['min_disbursement']):'');
                $config['max_disbursement']=array('field_type'=>'text_field','label'=>'Max. Disbursement','type'=>'text','class'=>'text number','required'=>'required','value'=>isset($_POST['max_disbursement'])?number_format($_POST['max_disbursement']):'');
                $config['comment']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$_POST['comment']??'','cols'=>300,'rows'=>3);
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New Wallet';
                $vars['submit_url']= base_url("wallets/new_wallet");
                $vars['content_view']='form';
                $vars['title']='New Wallet';
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