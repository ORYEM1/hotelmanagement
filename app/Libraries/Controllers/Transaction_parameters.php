<?php

namespace App\Controllers;

class Transaction_parameters extends RestrictedBaseController
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
            $table='transaction_parameters';
            $_SESSION["search_{$table}"]=array();
            $_SESSION["search_{$table}_where_in"]=array();
            $_SESSION["search_{$table}_like"]=array();

            if(!empty($_GET['lib_id']))
            {
                $where['library_id']=$_GET['lib_id'];
            }
            $params=array();
            $params['table_name']=$table;
            $params['where']=$where??array();
            $params['where_search_fields']=array('id'=>'id','name'=>'name','library_id'=>'library_id');
            $this->set_search_data($params);

            $vars['content_view']='data_table';
            $vars['title']='Transaction Parameters';
            $vars['page_heading']='Transaction Parameters';

            //Data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Library','sortable'=>true,'db_col_name'=>'wallet_libraries.readable_name');
            $data_header[]=array('name'=>'Name','sortable'=>false);
            $data_header[]=array('name'=>'Value','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            //============================================================================================
            $data_footer[]=get_advanced_search_button();
            $url="/transaction_parameters/new_parameter";
            if(isset($_GET['lib_id']))
            {
                $url.="?lib_id=".$_GET['lib_id'];
            }
            $data_footer[]=get_new_link_button(array('url'=>$url,'label'=>'Add Parameter','icon'=>'fa fa-plus'));
            $actions['Delete Selected']='delete_selected_transaction_parameters';
            $data_footer[]=get_actions_field(array('options'=>$actions));
            $vars['data_footer']=$data_footer;

            //Data tables options
            //============================================================================================
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_transaction_parameters'),'bFilter'=>true,'order_columns'=>array('id'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //Advance search options
            //============================================================================================
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Parameter ID','name'=>'id','type'=>'number'));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Parameter Name','name'=>'name','type'=>'text'));
            $options=$this->base_model->get_form_options(array('table'=>'wallet_libraries','fields'=>array('id','readable_name'),'order'=>'readable_name'),'id','readable_name');
            $advanced_search_fields[]=$this->advanced_search->get_select_search(array('label'=>'Library','name'=>'library_id','options'=>$options));
            $vars['advanced_search_fields']=$advanced_search_fields;
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view('page',$vars);
    }
    public function view_parameter_value($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id', 'name', 'value');
            $data=$this->base_model->get_data(array('table'=>'transaction_parameters','fields'=>$fields,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['content_view']='transaction_parameters/view_parameter_value';
                $vars['title']=$data['name'];
                $vars['page_heading']=$data['name'];
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function view_parameter($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id','name','value','date_time_created','date_time_updated', 'wallet_libraries.readable_name', "CONCAT(users.first_name,' ',users.other_names) AS created_by");
            $join[]=array('table'=>'wallet_libraries','condition'=>'transaction_parameters.library_id=wallet_libraries.id','type'=>'left');
            $join[]=array('table'=>'users','condition'=>'transaction_parameters.created_by=users.id','type'=>'left');
            $data=$this->base_model->get_data(array('table'=>'transaction_parameters','fields'=>$fields, 'join'=>$join,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['content_view']='transaction_parameters/view_parameter';
                $vars['title']="View Parameter";
                $vars['page_heading']="View Parameter";
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_parameter($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit parameter')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array('library_id' =>'required|numeric|greater_than[0]','name'=>'required','value'=>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'transaction_parameters','where'=>array('library_id'=>$this->request->getPost('library_id'),'name'=>$this->request->getPost('name'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The parameter name {$this->request->getPost('name')} is already created for this library.")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The parameter name {$this->request->getPost('name')} is already created for this library.")));
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
                
                $this->base_model->update_data(array('table'=>'transaction_parameters','where'=>array('id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Parameter updated successfully")));
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
                $data=$this->base_model->get_data(array('table'=>'transaction_parameters','where'=>array('id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $options=$this->base_model->get_form_options(array('table'=>'wallet_libraries','fields'=>array('id','readable_name'),'order'=>'readable_name'),'id','readable_name');
                    $config['library_id']=array('field_type'=>'select_field','autofocus'=>'autofocus','label'=>'Library','options'=>$options,'required'=>'required','value'=>$data['lib_id']??'');
                    $config['name']=array('field_type'=>'text_field','label'=>'Name','type'=>'text','required'=>'required','value'=>$data['name']??'');
                    $config['value']=array('field_type'=>'textarea','label'=>'Value','required'=>'required','type'=>'text','value'=>$data['value']??'','cols'=>300,'rows'=>3);
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Parameter';
                    $vars['submit_url']= "/transaction_parameters/edit_parameter/{$data['id']}";
                    $vars['content_view']='form';
                    $vars['title']='Edit Parameter';
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
    public function new_parameter()
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission required to add a parameter')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('library_id' =>'required|numeric|greater_than[0]','name'=>'required','value'=>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'transaction_parameters','where'=>array('account_id'=>$this->request->getPost('account_id'),'name'=>$this->request->getPost('name'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The parameter name {$this->request->getPost('name')} is already created for this account.")));
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
                $data['date_time_created']=date('Y-m-d H:i:s');
                $id=$this->base_model->insert_data('transaction_parameters',$data);
                exit(json_encode(array('status'=>1,'msg'=>"Parameter added successfully. ID:{$id}")));
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
                $options=$this->base_model->get_form_options(array('table'=>'wallet_libraries','fields'=>array('id','readable_name'),'order'=>'readable_name'),'id','readable_name');
                $config['library_id']=array('field_type'=>'select_field','autofocus'=>'autofocus','label'=>'Library','options'=>$options,'required'=>'required','value'=>$_GET['lib_id']??'');
                $config['name']=array('field_type'=>'text_field','label'=>'Name','type'=>'text','required'=>'required','value'=>$_POST['name']??'');
                $config['value']=array('field_type'=>'textarea','label'=>'Value','required'=>'required','type'=>'text','value'=>$_POST['value']??'','cols'=>300,'rows'=>3);
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='Add Parameter';
                $vars['submit_url']= "/transaction_parameters/new_parameter";
                $vars['content_view']='form';
                $vars['title']='Add Parameter';
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