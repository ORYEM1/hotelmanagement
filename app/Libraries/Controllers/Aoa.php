<?php
namespace App\Controllers;
class Aoa extends BaseController
{
    private \App\Libraries\UG_Airtel_Open_API $library;
    public function __construct()
    {
        $this->library=new \App\Libraries\UG_Airtel_Open_API();
    }
    
    public function getTest()
    {
        $token=$this->library->get_access_token();
        //exit("Disabled");
         //Request payment
        $transaction_data=array();
        $date=date('Y-m-d');
        $time=date('H:i:s');
        $account_id=1;
        $account_data=$this->base_model->get_data(array('table'=>'airtel_open_api_accounts','where'=>array('id'=>$account_id)),true);
        if(empty($account_data))
        {
            exit("Account ID not found");
        }
        $wallet_data=$this->base_model->get_data(array('table'=>'wallets','where'=>array('id'=>$account_data['wallet_id'])),true);
        $transaction_data['api']=$this->api;
        $transaction_data['app_id']=1;
        $transaction_data['account_id']=$account_id;
        $transaction_data['date']=$date;
        $transaction_data['time']=$time;
        $transaction_data['timestamp']=$date.' '.$time;
        $transaction_data['amount']=1000;
        $transaction_data['sender_account']=256700655856;
        $transaction_data['recipient_account']=$account_data['msisdn'];
        $transaction_data['wallet']=$wallet_data['id']; 
        $transaction_data['currency']=$wallet_data['currency']; 
        $transaction_data['country_id']=$wallet_data['country_id']; 
        $transaction_data['internal_ref']=get_uuid(); 
        $transaction_data['random_ref']=random_string('alnum',25);
        $transaction_data['payer_ref']="Payment";
        $transaction_data['ext_app_ref']=null;
        $transaction_data['notify_url']=null;
        //print_r($transaction_data); exit;
        $transaction_data['id']=$this->base_model->insert_data('collection_requests',$transaction_data);  
        $res=$this->lib->request_payment($transaction_data);
        if(isset($res['status'])&&$res['status']==0)
        {
            $update_data['transaction_status']='FAILED';
            $update_data['status_code']=2;
            $update_data['comment']=$res['msg'];
            $update_data['user_msg']=$res['user_msg'];
            $update_data['wallet_response_code']=isset($res['wallet_response_code'])?$res['wallet_response_code']:0;
            $update_data['wallet_response_message']=isset($res['wallet_response_message'])?$res['wallet_response_message']:null;
            $this->base_model->update_data(array('table'=>'collection_requests','where'=>array('id'=>$transaction_data['id']),'data'=>$update_data));
        }
        if(isset($res['status'])&&$res['status']==1)
        {  
            $update_data['comment']=$res['msg'];
            $update_data['user_msg']=$res['user_msg'];
            $update_data['wallet_response_code']=isset($res['wallet_response_code'])?$res['wallet_response_code']:0;
            $update_data['wallet_response_message']=isset($res['wallet_response_message'])?$res['wallet_response_message']:null;
            $this->base_model->update_data(array('table'=>'collection_requests','where'=>array('id'=>$transaction_data['id']),'data'=>$update_data));
        }
        print_r($res); exit;
        
        //Send Payment
        /*$transaction_data=array();
        $date=date('Y-m-d');
        $time=date('H:i:s');
        $account_id=2;
        $account_data=$this->base_model->get_data(array('table'=>'airtel_open_api_accounts','where'=>array('id'=>$account_id)),true);
        if(empty($account_data))
        {
            exit("Account ID not found");
        }
        $wallet_data=$this->base_model->get_data(array('table'=>'wallets','where'=>array('id'=>$account_data['wallet_id'])),true);
        $transaction_data['api']=$this->api;
        $transaction_data['app_id']=1;
        $transaction_data['initiated_by']='app';
        $transaction_data['timestamp']=$date.' '.$time;
        $transaction_data['account_id']=$account_id;
        $transaction_data['date']=$date;
        $transaction_data['time']=$time;
        $transaction_data['amount']=1000;
        $transaction_data['sender_account']=$account_data['msisdn'];
        $transaction_data['recipient_account']=256700655856;
        $transaction_data['wallet']=$wallet_data['id']; 
        $transaction_data['currency']=$wallet_data['currency']; 
        $transaction_data['country_id']=$wallet_data['country_id']; 
        $transaction_data['internal_ref']=get_uuid(); 
        $transaction_data['random_ref']=random_string('alnum',20);
        $transaction_data['payer_ref']="Payment";
        $transaction_data['ext_app_ref']=null;
        $transaction_data['notify_url']=null;
        $transaction_data['id']=$this->base_model->insert_data('disbursement_requests',$transaction_data); 
        $res=$this->lib->send_payment($transaction_data);
        if(isset($res['status'])&&$res['status']==0)
        {
            $update_data['transaction_status']='FAILED';
            $update_data['status_code']=2;
            $update_data['comment']=$res['msg'];
            $update_data['user_msg']=$res['user_msg'];
            $update_data['wallet_response_code']=isset($res['wallet_response_code'])?$res['wallet_response_code']:0;
            $update_data['wallet_response_message']=isset($res['wallet_response_message'])?$res['wallet_response_message']:null;
            $this->base_model->update_data(array('table'=>'disbursement_requests','where'=>array('id'=>$transaction_data['id']),'data'=>$update_data));
        }
        if(isset($res['status'])&&$res['status']==1)
        {  
            $update_data['comment']=$res['msg'];
            $update_data['user_msg']=$res['user_msg'];
            $update_data['wallet_response_code']=isset($res['wallet_response_code'])?$res['wallet_response_code']:0;
            $update_data['wallet_response_message']=isset($res['wallet_response_message'])?$res['wallet_response_message']:null;
            $this->base_model->update_data(array('table'=>'disbursement_requests','where'=>array('id'=>$transaction_data['id']),'data'=>$update_data));
        }
        if(isset($res['status'])&&$res['status']==200)
        {
            if(!is_null($transaction_data['notify_url']))
            {
                $api->send_ipn('disbursement',array($transaction_data['id']));
            }
        }
        //Failed at wallet
        if(isset($res['status'])&&$res['status']==500)
        {
            if(!is_null($transaction_data['notify_url']))
            {
                $api->send_ipn('disbursement',array($transaction_data['id']));
            }
        }
        print_r($res); exit;*/
        
        //Get disbursement account balance
        /*$account_id=1;
        $res=$this->mtn->get_collection_account_balance($account_id);
        print_r($res); exit;*/
        
        //Send IPN
        $this->mtn->send_ipn('collection',32);
    }
    
    public function rpc()
    {
        $request= file_get_contents('php://input');
        $this->lib->request_payment_completed($request); 
    }
    
    public function spc()
    {
        $request= file_get_contents('php://input');
        $this->lib->send_payment_completed($request); 
    }
    
    public function update_collection_status($account_id='',$transaction_id='')
    {
         if(empty($account_id))
         {
             exit("Account ID not set");
         }
         $account_data=$this->base_model->get_data(array('table'=>'airtel_open_api_accounts','where'=>array('id'=>$account_id)),true);
         if(empty($account_data))
         {
            exit("Account ID not found");
         }
         $start_time=date('Y-m-d H:i:s',time()-86400);
         $end_time=date('Y-m-d H:i:s',time()-50);
         
         $where=array('transaction_status'=>'PENDING','api'=>$this->api,'account_id'=>$account_id,'timestamp >='=>$start_time,'timestamp <='=>$end_time);
         if($transaction_id!='')
         {
             $where=array('id'=>$transaction_id,'account_id'=>$account_id,'api'=>$this->api);
         }
         $pending_transactions=$this->base_model->get_data(array('table'=>'collection_requests','where'=>$where,'limit'=>100));
         if(empty($pending_transactions)) exit('No pending transactions found');
         $res=$this->lib->get_collection_status($account_id,$pending_transactions);
         $counter=count($pending_transactions);
         exit("{$res} transactions updated");
    }
    
     public function update_disbursement_status($account_id='',$transaction_id='')
    {
         if(empty($account_id))
         {
             exit("Account ID not set");
         }
         $account_data=$this->base_model->get_data(array('table'=>'airtel_open_api_accounts','where'=>array('id'=>$account_id)),true);
         if(empty($account_data))
         {
            exit("Account ID not found");
         }
         
         $start_time=date('Y-m-d H:i:s',time()-86400);
         $end_time=date('Y-m-d H:i:s',time()-0);
         
         $where=array('transaction_status'=>'PENDING','api'=>$this->api,'timestamp >='=>$start_time,'timestamp <='=>$end_time);
         if($transaction_id!='')
         {
             $where=array('id'=>$transaction_id,'api'=>$this->api);
         }
         
         $pending_transactions=$this->base_model->get_data(array('table'=>'disbursement_requests','where'=>$where,'limit'=>100)); 
         if(empty($pending_transactions)) exit('No pending transactions found'); 
         $res=$this->lib->get_disbursement_status($account_id,$pending_transactions);
         exit("{$res} transactions updated");
    }
    
    public function check_balance($account_id='')
    {
         if($account_id=='')
         {
             exit("Account ID is required");
         }
         $account_data=$this->base_model->get_data(array('table'=>'airtel_open_api_accounts','where'=>array('id'=>$account_id)),true);
         if(empty($account_data))
         {
             exit("Account ID not found");
         }
         $res=$this->lib->get_account_balance($account_id);
         print_r($res); exit;
    }
    
    public function get_customer_data($account_id='',$msisdn='')
    {
         if($account_id=='')
         {
             exit("Account ID is required");
         }
         if($msisdn=='')
         {
             exit("MSISDN is required");
         }
         $account_data=$this->base_model->get_data(array('table'=>'airtel_open_api_accounts','where'=>array('id'=>$account_id)),true);
         if(empty($account_data))
         {
             exit("Account ID not found");
         }
         $res=$this->lib->get_customer_data($account_id,$msisdn);
         print_r($res); exit;
    }
    
    public function refund($transaction_id=0)
    {
        $res=$this->lib->refund($transaction_id);
        print_r($res); exit;
    }
    
}
