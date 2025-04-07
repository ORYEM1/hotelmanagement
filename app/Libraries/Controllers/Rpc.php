<?php
namespace App\Controllers;
class Rpc extends RestrictedBaseController
{
    private string $controller;
    public function __construct()
    {
        $this->controller = strtolower((new \ReflectionClass($this))->getShortName());
    }
    function getGet_base_role_checklist()
    {
        if(empty($_GET['name']))
        {
            exit('');
        }
        $name=$_GET['name'];
        $role_data=array();
        if(!empty($_GET['value']))
        {
            $role_data=$this->base_model->get_data(array('table'=>'user_roles','where'=>array('id'=>$_GET['value'])),true);
        }
        $config[$name]=array('field_type'=>'checklist','label'=>$name,'options'=>get_permissions(),'value'=>$role_data['rights']??'');
        $form_data=get_form_data($config);
        $form_data=array_pop($form_data);
        exit(get_checklist_items($form_data['name'],$form_data['options']));
    }
    function getGet_gateway_functions_checklist()
    {
        if(empty($_GET['name'])||empty($_GET['value']))
        {
            exit('');
        }
        $name=$_GET['name'];
        $library=trim($_GET['value']);
        $current_value=$_GET['initial_value']??null;
        $options=get_gateway_functions($library);
        $config[$name]=array('field_type'=>'checklist','label'=>$name,'options'=>$options,'value'=>$current_value);
        $form_data=get_form_data($config);
        $form_data=array_pop($form_data);
        exit(get_checklist_items($form_data['name'],$form_data['options']));
    }
    public function getGet_wallet_options()
    {
        $params=array();
        $query_params=array('table'=>'wallets','order'=>'wallet_name');
        if(!empty($_GET['current_value']))
        {
            $query_params['where']=array('country_id'=>$_GET['current_value']);
        }
        $options=$this->base_model->get_form_options($query_params,'id','wallet_name');
        exit(get_select_options($params,$options));
    }
    public function postDelete_selected_logged_numbers()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $affected_rows=$this->base_model->delete_data('logged_numbers',array('where_in'=>array('id'=>$ids)));
            $msg="{$affected_rows} record(s) deleted";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postActivate_selected_wallets()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $table='wallets';
            $update=array();
            foreach($ids as $id)
            {
                $update[]=array('id'=>$id,'status'=>1);
            }
            if(!empty($update))
            {
                $affected_rows=$this->base_model->update_data_batch($table,$update,'id',true);
            }
            else
            {
                $affected_rows=0;
            }
            $msg="{$affected_rows} record(s) activated";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postDeactivate_selected_wallets()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $table='wallets';
            $update=array();
            foreach($ids as $id)
            {
                $update[]=array('id'=>$id,'status'=>0);
            }
            if(!empty($update))
            {
                $affected_rows=$this->base_model->update_data_batch($table,$update,'id',true);
            }
            else
            {
                $affected_rows=0;
            }
            $msg="{$affected_rows} record(s) deactivated";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postActivate_collection_for_selected_wallets()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $table='wallets';
            $update=array();
            foreach($ids as $id)
            {
                $update[]=array('id'=>$id,'collection_status'=>1);
            }
            if(!empty($update))
            {
                $affected_rows=$this->base_model->update_data_batch($table,$update,'id',true);
            }
            else
            {
                $affected_rows=0;
            }
            $msg="Collection status activated for {$affected_rows} record(s)";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postDeactivate_collection_for_selected_wallets()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $table='wallets';
            $update=array();
            foreach($ids as $id)
            {
                $update[]=array('id'=>$id,'collection_status'=>0);
            }
            if(!empty($update))
            {
                $affected_rows=$this->base_model->update_data_batch($table,$update,'id',true);
            }
            else
            {
                $affected_rows=0;
            }
            $msg="Collection status deactivated for {$affected_rows} record(s)";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postActivate_disbursement_for_selected_wallets()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $table='wallets';
            $update=array();
            foreach($ids as $id)
            {
                $update[]=array('id'=>$id,'disbursement_status'=>1);
            }
            if(!empty($update))
            {
                $affected_rows=$this->base_model->update_data_batch($table,$update,'id',true);
            }
            else
            {
                $affected_rows=0;
            }
            $msg="Disbursement status activated for {$affected_rows} record(s)";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postDeactivate_disbursement_for_selected_wallets()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $table='wallets';
            $update=array();
            foreach($ids as $id)
            {
                $update[]=array('id'=>$id,'disbursement_status'=>0);
            }
            if(!empty($update))
            {
                $affected_rows=$this->base_model->update_data_batch($table,$update,'id',true);
            }
            else
            {
                $affected_rows=0;
            }
            $msg="Disbursements deactivated for {$affected_rows} record(s)";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postDelete_selected_currencies()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $affected_rows=$this->base_model->delete_data('supported_currencies',array('where_in'=>array('id'=>$ids)));
            $msg="{$affected_rows} record(s) deleted";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postDelete_selected_prefixes()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $affected_rows=$this->base_model->delete_data('mobile_network_prefixes',array('where_in'=>array('id'=>$ids)));
            $msg="{$affected_rows} record(s) deleted";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postDelete_selected_ip_pools()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $affected_rows=$this->base_model->delete_data('whitelisted_user_ips',array('where_in'=>array('id'=>$ids)));
            $msg="{$affected_rows} record(s) deleted";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postActivate_selected_wallet_libraries()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $table='wallet_libraries';
            $update=array();
            foreach($ids as $id)
            {
                $update[]=array('id'=>$id,'status'=>1);
            }
            if(!empty($update))
            {
                $affected_rows=$this->base_model->update_data_batch($table,$update,'id',true);
            }
            else
            {
                $affected_rows=0;
            }
            $msg="{$affected_rows} record(s) activated";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postDeactivate_selected_wallet_libraries()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $table='wallet_libraries';
            $update=array();
            foreach($ids as $id)
            {
                $update[]=array('id'=>$id,'status'=>0);
            }
            if(!empty($update))
            {
                $affected_rows=$this->base_model->update_data_batch($table,$update,'id',true);
            }
            else
            {
                $affected_rows=0;
            }
            $msg="{$affected_rows} record(s) deactivated";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postActivate_logging_for_selected_wallet_libraries()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $table='wallet_libraries';
            $update=array();
            foreach($ids as $id)
            {
                $update[]=array('id'=>$id,'log_requests'=>1);
            }
            if(!empty($update))
            {
                $affected_rows=$this->base_model->update_data_batch($table,$update,'id',true);
            }
            else
            {
                $affected_rows=0;
            }
            $msg="Logging activated for {$affected_rows} record(s)";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postDeactivate_logging_for_selected_wallet_libraries()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $table='wallet_libraries';
            $update=array();
            foreach($ids as $id)
            {
                $update[]=array('id'=>$id,'log_requests'=>0);
            }
            if(!empty($update))
            {
                $affected_rows=$this->base_model->update_data_batch($table,$update,'id',true);
            }
            else
            {
                $affected_rows=0;
            }
            $msg="Logging deactivated for {$affected_rows} record(s)";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postActivate_selected_transaction_accounts()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $table='transaction_accounts';
            $update=array();
            foreach($ids as $id)
            {
                $update[]=array('id'=>$id,'status'=>1);
            }
            if(!empty($update))
            {
                $affected_rows=$this->base_model->update_data_batch($table,$update,'id',true);
            }
            else
            {
                $affected_rows=0;
            }
            $msg="{$affected_rows} account(s) activated";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
    public function postDeactivate_selected_transaction_accounts()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $table='transaction_accounts';
            $update=array();
            foreach($ids as $id)
            {
                $update[]=array('id'=>$id,'status'=>0);
            }
            if(!empty($update))
            {
                $affected_rows=$this->base_model->update_data_batch($table,$update,'id',true);
            }
            else
            {
                $affected_rows=0;
            }
            $msg="{$affected_rows} account(s) deactivated";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }

    public function postDelete_selected_transaction_parameters()
    {
        if(!user_has_access($this->controller,__FUNCTION__))
        {
            exit(json_encode(array('status'=>0,'msg'=>'You do not have access to the requested function')));
        }
        if($this->request->getPost('record_ids'))
        {
            $ids=json_decode($this->request->getPost('record_ids'),true);
            $affected_rows=$this->base_model->delete_data('transaction_parameters',array('where_in'=>array('id'=>$ids)));
            $msg="{$affected_rows} record(s) deleted";
            exit(json_encode(array('status'=>1,'msg'=>$msg)));
        }
    }
}
