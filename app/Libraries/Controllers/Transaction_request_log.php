<?php

namespace App\Controllers;

class Transaction_request_log extends RestrictedBaseController
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
            $table='transaction_request_log';
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
                $params['where_search_fields']=array('id'=>'id','request_id'=>'request_id','transacting_number'=>'transacting_number','library_id'=>'library_id','function'=>'function');
                $params['like_search_fields']=array('date'=>'date_time');
                $params['where_in_search_fields']=array('initiated_by'=>'initiated_by');
                $search_range=array();
                $search_range['from_date_time']=array('column'=>'date_time','operator'=>'>=');
                $search_range['to_date_time']=array('column'=>'date_time','operator'=>'<=');
                $params['search_range']=$search_range;
                $this->set_search_data($params);
            }
            else
            {
                $like['date_time']=date('Y-m-d');
                $params=array();
                $params['table_name']=$table;
                $params['where']=$where??array();
                $params['like']=$like??array();
                $params['where_search_fields']=array('library_id'=>'library_id');
                $params['like_search_fields']=array('date'=>'date_time');
                $this->set_search_data($params);
            }

            $vars['content_view']='data_table';
            $vars['title']='Transaction Request Log';
            $vars['page_heading']='Transaction Request Log';

            //Data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Date & Time','sortable'=>true,'db_col_name'=>'date_time');
            $data_header[]=array('name'=>'Request ID','sortable'=>false);
            $data_header[]=array('name'=>'Transacting Number','sortable'=>false);
            $data_header[]=array('name'=>'Library','sortable'=>false);
            $data_header[]=array('name'=>'Initiated By','sortable'=>false);
            $data_header[]=array('name'=>'Function','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            //============================================================================================
            $data_footer[]=get_advanced_search_button();
            $vars['data_footer']=$data_footer;

            //Data tables options
            //============================================================================================
            $dt_params=array('ajax'=>base_url('data_tables/get_data/get_transaction_request_log'),'bFilter'=>false,'order_columns'=>array('ID'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //Advance search options
            //============================================================================================
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'ID','name'=>'id','type'=>'number'));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Request ID','name'=>'request_id','type'=>'number'));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Transacting Number','name'=>'transacting_number','type'=>'text'));
            $options=$this->base_model->get_form_options(array('table'=>'wallet_libraries','fields'=>array('id','readable_name'),'order'=>'readable_name'),'id','readable_name');
            $advanced_search_fields[]=$this->advanced_search->get_select_search(array('label'=>'Library','name'=>'library_id','options'=>$options));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Function','name'=>'function','type'=>'text'));
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Date','name'=>'date','type'=>'date'));
            $advanced_search_fields[]=$this->advanced_search->get_date_time_range();
            $advanced_search_fields[]=$this->advanced_search->get_checklist_search(array('label'=>"Initiated By",'name'=>'initiated_by','options'=>array('app','wallet')));
            $vars['advanced_search_fields']=$advanced_search_fields;
        }
        else
        {
            $vars['content_view']='unauthorized';
            $vars['title']='401 Unauthorized';
        }
        return view('page',$vars);
    }
    public function view_log($id=0)
    {
        if(user_has_access($this->controller,__FUNCTION__))
        {
            $fields=array('id', 'transacting_number', 'library_id', 'request_id','request','response', 'date_time', 'initiated_by', 'function','wallet_libraries.readable_name');
            $join=array(array('table'=>'wallet_libraries','condition'=>'transaction_request_log.library_id=wallet_libraries.id','type'=>'left'));
            $data=$this->base_model->get_data(array('table'=>'transaction_request_log','fields'=>$fields, 'join'=>$join,'where'=>array('id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['content_view']='transaction_request_log/view_log';
                $vars['title']="View request log";
                $vars['page_heading']="View Request Log";
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