<?php

namespace App\Controllers;

class Countries extends RestrictedBaseController
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
            $table='countries';
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
                $params['where_in_search_fields']=array('supported'=>'supported','country_code'=>'country_code','dialing_code'=>'dialing_code');
                $this->set_search_data($params);
            }
            else
            {
                $params=array();
                $where_in['supported']=$_POST['supported']=array('1');
                $params['table_name']=$table;
                $params['where_in']=$where_in;
                $params['where_in_search_fields']=array('supported'=>'supported');
                $this->set_search_data($params);
            }
            $vars['content_view']='data_table';
            $vars['title']='Countries';
            $vars['page_heading']='Countries';

            //Data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Country','sortable'=>true,'db_col_name'=>'country');
            $data_header[]=array('name'=>'Country Code','sortable'=>true,'db_col_name'=>'country_code');
            $data_header[]=array('name'=>'Dialing Code','sortable'=>true,'db_col_name'=>'dialing_code');
            $data_header[]=array('name'=>'Supported','sortable'=>false);
            $data_header[]=array('name'=>'Phone Length','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $data_header[]=array('name'=>'<input type="checkbox" class="check_all_boxes" data-target_class="select_record" title="Select All" />','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            //============================================================================================
            $data_footer[]=get_advanced_search_button();
            $data_footer[]=get_new_link_button(array('url'=>"/countries/new_country",'label'=>'New Country','icon'=>'fa fa-plus'));
            $vars['data_footer']=$data_footer;

            //Data tables options
            //============================================================================================
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_countries'),'bFilter'=>true,'order_columns'=>array('Country'=>'asc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //Advance search options
            //============================================================================================
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Country ID','name'=>'id','type'=>'number'));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Country Code','name'=>'country_code','type'=>'text'));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Dialing Code','name'=>'dialing_code','type'=>'number'));
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>'Supported','name'=>'supported','options'=>get_statuses_array()));
            $vars['advanced_search_fields']=$advanced_search_fields;
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view('page',$vars);
    }
    public function view_country($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id', 'country_code', 'dialing_code', 'telephone_number_length', 'country', 'nationality', 'supported', 'comment', "CONCAT(users.first_name,' ',users.other_names) AS created_by");
            $join[]=array('table'=>'users','on'=>'users.id=countries.created_by','type'=>'left');
            $data=$this->base_model->get_data(array('table'=>'countries','join'=>$join,'fields'=>$fields,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['statuses']=get_statuses_array(true);
                $vars['content_view']='countries/view_country';
                $vars['title']='Country';
                $vars['page_heading']='Country';
            }
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view($vars['content_view'],$vars);
    }
    public function edit_country($id=0)
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            $data=$this->base_model->get_data(array('table'=>'countries','where'=>array('id'=>$id)),true);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission to edit country')));
            }

            $validation = \Config\Services::validation();
            $validation_rules=array('country' =>'required','nationality' =>'required','supported'=>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'countries','where'=>array('country'=>$this->request->getPost('country'))));
                if(count($existing_data)>1)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The country name {$this->request->getPost('country')} is already in the database")));
                }
                else if(isset($existing_data[0]['id'])&&$existing_data[0]['id']!=$id)
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The country name {$this->request->getPost('country')} is already assigned to another country")));
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
                
                $this->base_model->update_data(array('table'=>'countries','where'=>array('id'=>$id),'data'=>$data),true);
                exit(json_encode(array('status'=>1,'msg'=>"Country updated successfully")));
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
                $data=$this->base_model->get_data(array('table'=>'countries','where'=>array('id'=>$id)),true);
                if(empty($data))
                {
                    $vars['content_view']='not_found';
                    $vars['title']='404 Not Found';
                }
                else
                {
                    $config=array();
                    $config['country']=array('field_type'=>'text_field','label'=>'Country Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$data['country']??'');
                    $config['nationality']=array('field_type'=>'text_field','label'=>'Nationality','type'=>'text','required'=>'required','value'=>$data['nationality']??'');
                    $config['country_code']=array('field_type'=>'text_field','label'=>'Country Code','type'=>'text','value'=>$data['country_code']??'');
                    $config['dialing_code']=array('field_type'=>'text_field','label'=>'Dialing Code','type'=>'number','value'=>$data['dialing_code']??'');
                    $config['telephone_number_length']=array('field_type'=>'text_field','label'=>'Phone Number Length','type'=>'number','value'=>$data['telephone_number_length']??'');
                    $config['supported']=array('field_type'=>'select_field','label'=>'Supported','required'=>'required','options'=>get_statuses_array(),'value'=>$data['supported']??'');
                    $config['comment']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$data['comment']??'','cols'=>300,'rows'=>3);
                    $vars['form_data']=get_form_data($config);
                    $vars['form_title']='Edit Country';
                    $vars['submit_url']= base_url("countries/edit_country/{$data['id']}");
                    $vars['content_view']='form';
                    $vars['title']='Edit Country';
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
    public function new_country()
    {
        if($this->request->getPost('submit'))
        {
            unset($_POST['submit']);
            unset($_POST['base_role']);
            if(!user_has_access($this->controller,__FUNCTION__))
            {
                exit(json_encode(array('status'=>0,'msg'=>'You do not have permission required to add a country')));
            }
            $validation = \Config\Services::validation();
            $validation_rules=array('country' =>'required','nationality' =>'required','supported'=>'required');
            $validation->setRules($validation_rules);
            if($validation->withRequest($this->request)->run())
            {
                $existing_data=$this->base_model->get_data(array('table'=>'countries','where'=>array('country'=>$this->request->getPost('country'))));
                if(!empty($existing_data))
                {
                    exit(json_encode(array('status'=>0,'msg'=>"The country name {$this->request->getPost('country')} is already assigned to another country")));
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
                $id=$this->base_model->insert_data('countries',$data);
                exit(json_encode(array('status'=>1,'msg'=>"Country added successfully. ID:{$id}")));
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
                $config['country']=array('field_type'=>'text_field','label'=>'Country Name','type'=>'text','autofocus'=>'autofocus','required'=>'required','value'=>$_POST['country']??'');
                $config['nationality']=array('field_type'=>'text_field','label'=>'Nationality','type'=>'text','required'=>'required','value'=>$_POST['nationality']??'');
                $config['country_code']=array('field_type'=>'text_field','label'=>'Country Code','type'=>'text','value'=>$_POST['country_code']??'');
                $config['dialing_code']=array('field_type'=>'text_field','label'=>'Dialing Code','type'=>'number','value'=>$_POST['dialing_code']??'');
                $config['telephone_number_length']=array('field_type'=>'text_field','label'=>'Phone Number Length','type'=>'number','value'=>$_POST['telephone_number_length']??'');
                $config['supported']=array('field_type'=>'select_field','label'=>'Supported','required'=>'required','options'=>get_statuses_array(),'value'=>$_POST['supported']??'');
                $config['comment']=array('field_type'=>'textarea','label'=>'Comment','type'=>'text','value'=>$_POST['comment']??'','cols'=>300,'rows'=>3);
                $vars['form_data']=get_form_data($config);
                $vars['form_title']='New Country';
                $vars['submit_url']= base_url("countries/new_country");
                $vars['content_view']='form';
                $vars['title']='New Country';
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