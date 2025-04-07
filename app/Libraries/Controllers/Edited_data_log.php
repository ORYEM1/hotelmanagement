<?php

namespace App\Controllers;

class Edited_data_log extends RestrictedBaseController
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
            $table='edited_data_log';
            $_SESSION["search_{$table}"]=array();
            $_SESSION["search_{$table}_where_in"]=array();
            $_SESSION["search_{$table}_like"]=array();
            if($this->request->getPost('search'))
            {
                if($this->request->getPost('date'))
                {
                    unset($_POST['from_date_time']);
                    unset($_POST['to_date_time']);
                }
                $params=array();
                $params['table_name']=$table;
                $params['where']=$where??array();
                $params['where_in']=$where_in??array();
                $params['where_search_fields']=array('id'=>'id','record_id'=>'record_id','table'=>'table','user_id'=>'user_id');
                $params['like_search_fields']=array('date'=>'date_time');
                $search_range=array();
                $search_range['from_date_time']=array('column'=>'date_time','operator'=>'>=');
                $search_range['to_date_time']=array('column'=>'date_time','operator'=>'<=');
                $params['search_range']=$search_range;
                $this->set_search_data($params);
            }
            $vars['content_view']='data_table';
            $vars['title']='Edited Data Log';
            $vars['page_heading']='Edited Data Log';

            //Data header
            $data_header=array();
            $data_header[]=array('name'=>'ID','sortable'=>true,'db_col_name'=>'id');
            $data_header[]=array('name'=>'Date & Time','sortable'=>true,'db_col_name'=>'date_time');
            $data_header[]=array('name'=>'IP Address','sortable'=>false);
            $data_header[]=array('name'=>'Table','sortable'=>false);
            $data_header[]=array('name'=>'Record ID','sortable'=>false);
            $data_header[]=array('name'=>'User','sortable'=>false);
            $data_header[]=array('name'=>'','class'=>'icon_col','sortable'=>false);
            $vars['data_header']=$data_header;

            //Data Footer
            $data_footer=array();
            $data_footer[]=get_advanced_search_button();
            $vars['data_footer']=$data_footer;

            //Data tables options
            $dt_params=array('ajax'=>'/data_tables/get_data/get_edited_data_log','order_columns'=>array('ID'=>'desc'));
            $vars['data_tables_config']=get_dt_config($data_header,$dt_params);

            //Advance search options
            //============================================================================================
            //Search by ID
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'ID','name'=>'id','type'=>'number'));
            //Search by Record ID
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Record ID','name'=>'record_id','type'=>'number'));
            //Search by table name
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Table Name','name'=>'table','type'=>'text'));
            //Search by user ID
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'User ID','name'=>'id','type'=>'number'));
            //Search by Time Range
            $advanced_search_fields[]=$this->advanced_search->get_date_time_range(array('from_date_time'=>$_POST['from_date_time']??'','to_date_time'=>$_POST['to_date_time']??''));
            //Search by date
            $advanced_search_fields[]=$this->advanced_search->get_text_search(array('label'=>'Date','name'=>'date','type'=>'date'));

            if(!empty($advanced_search_fields))
            {
                $vars['advanced_search_fields']=$advanced_search_fields;
            }
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
            $fields=array('id','date_time','user_id','ip_address','table','record_id','update','users.first_name','users.other_names');
            $join[]=array('table'=>'users','condition'=>'edited_data_log.user_id=users.id','type'=>'left');
            $data=$this->base_model->get_data(array('table'=>'edited_data_log','fields'=>$fields,'join'=>$join,'where'=>array('edited_data_log.id'=>$id)),true);
            if(empty($data))
            {
                $vars['content_view']='not_found';
                $vars['title']='404 Not Found';
            }
            else
            {
                $vars['record']=$data;
                $vars['content_view']='edited_data_log/view_log';
                $vars['title']='Edited Data Log';
                $vars['page_heading']='Edited Data Log';
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