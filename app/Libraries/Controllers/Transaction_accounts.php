<?php

namespace App\Controllers;

class Transaction_accounts extends RestrictedBaseController
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
            $table='transaction_accounts';
            $_SESSION["search_{$table}"]=array();
            $_SESSION["search_{$table}_where_in"]=array();
            $_SESSION["search_{$table}_like"]=array();
            if(!empty($_GET['lib_id']))
            {
                $where['library_id']=$_GET['lib_id'];
            }
            if($this->request->getPost('search'))
            {
                $params=array();
                $params['table_name']=$table;
                $params['where']=$where??array();
                $params['where_in']=$where_in??array();
                $params['where_search_fields']=array('id'=>'id','account_number'=>'account_number','library_id'=>'library_id');
                $search_range=array();
                $search_range['from_date_time']=array('column'=>'date_time_created','operator'=>'>=');
                $search_range['to_date_time']=array('column'=>'date_time_created','operator'=>'<=');
                $params['search_range']=$search_range;
                $this->set_search_data($params);
            }
            else
            {
                $params=array();
                $params['where']=$where??array();
                $params['table_name']=$table;
                $params['where_search_fields']=array('library_id'=>'library_id');
                $this->set_search_data($params);
            }
            $vars['content_view']='data_table';
            $vars['title']='Transaction Accounts';
            $vars['page_heading']='Transaction Accounts';

            //Data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Account Number','sortable'=>false);
            $data_header[]=array('name'=>'Purpose','sortable'=>false);
            $data_header[]=array('name'=>'Library','sortable'=>true,'db_col_name'=>'wallet_libraries.readable_name');
            $data_header[]=array('name'=>'Account Status','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            //============================================================================================
            $data_footer[]=get_advanced_search_button();
            $url="/transaction_accounts/new_account";
            if(!empty($_GET['lib_id']))
            {
                $url.="?lib_id=".$_GET['lib_id'];
            }
            $data_footer[]=get_new_link_button(array('url'=>$url,'label'=>'New Account','icon'=>'fa fa-plus'));
            $actions['Activate Account']='activate_selected_transaction_accounts';
            $actions['Deactivate Account']='deactivate_selected_transaction_accounts';
            $data_footer[]=get_actions_field(array('options'=>$actions));
            $vars['data_footer']=$data_footer;
            $vars['data_footer']=$data_footer;

            //Data tables options
            //============================================================================================
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_transaction_accounts'),'bFilter'=>true,'order_columns'=>array('ID'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //Advance search options
            //============================================================================================
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Account ID','name'=>'id','type'=>'number'));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Account Number','name'=>'account_number','type'=>'text'));
            $options=$this->base_model->get_form_options(array('table'=>'wallet_libraries','fields'=>array('id','readable_name'),'order'=>'readable_name'),'id','readable_name');
            $advanced_search_fields[]=$this->advanced_search->get_select_search(array('label'=>'Library','name'=>'library_id','options'=>$options));
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Status','name'=>'status','options'=>get_statuses_array()));
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
    public function view_account($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id', 'library_id', 'account_number', 'account_name', 'purpose', 'created_by', 'date_time_created', 'status', 'wallet_libraries.readable_name',  "CONCAT(users.first_name,' ',users.other_names) AS created_by");
            $join[]=array('table'=>'users','on'=>'users.id=transaction_accounts.created_by','type'=>'left');
            $join[]=array('table'=>'wallet_libraries','on'=>'wallet_libraries.id=transaction_accounts.library_id','type'=>'left');
            $data=$this->base_model->get_data(array('table'=>'transaction_accounts','join'=>$join,'fields'=>$fields,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='transaction_accounts/view_account';
                $vars['title']='Transaction Account';
                $vars['page_heading']='Transaction Account';
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_account($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'transaction_accounts','where'=>array('id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit account')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array('account_number' =>'required','account_name' =>'required','purpose' =>'required','library_id' =>'required','status' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'transaction_accounts','where'=>array('account_number'=>$this->request->getPost('account_number'),'library_id'=>$this->request->getPost('library_id'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The account number {$this->request->getPost('account_number')} is already added for this library")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The account number {$this->request->getPost('account_number')} is already added for this library")));
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
                
                $this->base_model->update_data(array('table'=>'transaction_accounts','where'=>array('id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Account updated successfully")));
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
                $data=$this->base_model->get_data(array('table'=>'transaction_accounts','where'=>array('id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['account_number']=array('field_type'=>'text_field','label'=>'Account Number`','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$data['account_number']??'');
                    $config['account_name']=array('field_type'=>'text_field','label'=>'Account Name`','type'=>'text','required'=>'required','value'=>$data['account_name']??'');
                    $options=$this->base_model->get_form_options(array('table'=>'wallet_libraries','fields'=>array('id','readable_name'),'order'=>'readable_name'),'id','readable_name');
                    $config['library_id']=array('field_type'=>'select_field','label'=>'Library','options'=>$options,'required'=>'required','value'=>$data['library_id']??'');
                    $config['purpose']=array('field_type'=>'checklist','label'=>'Purpose','options'=>get_transaction_types(),'required'=>'required','value'=>$data['purpose']??'');
                    $config['status']=array('field_type'=>'select_field','label'=>'Account Status','options'=>get_statuses_array(),'required'=>'required','value'=>$data['status']??'');
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Account';
                    $vars['submit_url']= base_url("transaction_accounts/edit_account/{$data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Edit Account';
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
    public function new_account()
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission required to add an account')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('account_number' =>'required','account_name' =>'required','purpose' =>'required','library_id' =>'required','status' =>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'transaction_accounts','where'=>array('account_number'=>$this->request->getPost('account_number'),'library_id'=>$this->request->getPost('library_id'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The account number {$this->request->getPost('account_number')} is already added for this library")));
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
                $id=$this->base_model->insert_data('transaction_accounts',$data);
                exit(json_encode(array('status'=>1,'msg'=>"Account added successfully. ID:{$id}")));
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
                $config['account_number']=array('field_type'=>'text_field','label'=>'Account Number`','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['account_number']??'');
                $config['account_name']=array('field_type'=>'text_field','label'=>'Account Name`','type'=>'text','required'=>'required','value'=>$_POST['account_name']??'');
                $options=$this->base_model->get_form_options(array('table'=>'wallet_libraries','fields'=>array('id','readable_name'),'order'=>'readable_name'),'id','readable_name');
                $config['library_id']=array('field_type'=>'select_field','label'=>'Library','options'=>$options,'required'=>'required','value'=>$_GET['lib_id']??'');
                $config['purpose']=array('field_type'=>'checklist','label'=>'Purpose','options'=>get_transaction_types(),'required'=>'required','value'=>$_POST['purpose']??'');
                $config['status']=array('field_type'=>'select_field','label'=>'Account Status','options'=>get_statuses_array(),'required'=>'required','value'=>$_POST['status']??'');
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New Account';
                $vars['submit_url']= base_url("transaction_accounts/new_account");
                $vars['content_view']='form';
                $vars['title']='New Account';
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