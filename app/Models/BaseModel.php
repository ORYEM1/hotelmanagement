<?php
namespace  App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    protected  $db;
    private string $edited_data_log;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->edited_data_log="edited_data_log";
    }
    public  function insert_data()

    {
        $builder = $this->db->table($table);
        if(!empty($data))
        {
            $builder->insert($data,true);
            return $this->db->insertID();
        }
        return  false;

    }
    public function insert_data_batch($table,$data)
    {
        $builder = $this->db->table($table);
        if(!empty($data))
        {
            $builder->insertBatch($data,true);
            return $this->db->affectedRows();
        }
        return  false;
    }
    public function  insert_data_batch_transaction($table,$data)

    {
        $builder = $this->db->table($table);
        if(!empty($data))
        {
            $builder->insertBatch($data,true);
            return $this->db->affectedRows();
        }
        return  false;
    }
    public function insert_data_batch_transaction_ext($table,$data)
    {
        if(!empty($data))
        {
            $batches=array_chunk($data,10);
            $this->db->transStart();
            foreach($batches as $batch)
            {
                $insert_query = $this->generate_insert($batch);
                $this->db->query($insert_query);
            }
            $this->db->transComplete();
            return $this->db->affectedRows();
            return  true;
        }
        return  false;
    }
    public function update_data($query_parameters=array(),$keep_log=false)
    {
        if(empty($query_parameters['table'])) return false;
        if(empty($query_parameters['where'])) return false;
        if(empty($query_parameters['data'])) return false;

        if($keep_log&&isset($_SESSION['user_data']))
        {
            $old_data=$this->get_data(array('table'=>$query_parameters['table'],'where'=>$query_parameters['where'],'limit'=>1));
            $update=array();
            foreach($query_parameters['data'] as $data)
            {
                if(array_key_exists($key,$old_data))
                {
                    $update[$key]=array('old_data'=>$data,'new_data'=>$query_parameters['data'][$key]);

                }

            }
            if(!empty($update))
            {
                $edited_data_log=array('user_id'=>$_SESSION['user_data']['id'],'data'=>$query_parameters['data']);

            }
            $builder = $this->db->table($query_parameters['table']);
            $builder->where($query_parameters['where']);
            $query=$builder->update($query_parameters['table'],$update);
            if(!empty($edited_data_log))
            {
                $this->insert_data($this->edited_data_log_table,$edited_data_log)
            }
            return $this->db->affectedRows();
        }
    }
    public  function update_data_batch($table,$data,$id='id',$keep_log=false)
    {
        $affected=0;
        if(!empty($data))
        {
            if($keep_log&&isset($_SESSION['user_data']))
            {
                $edited_data_log=array();
                $ids=array_column($data,'id');
                $existing_data=$this->get_data(array('table'=>$table,'where_in'=>array('id'=>$ids)));
                if(!empty($existing_data))
                {
                    $ids=array_column($existing_data,'id');
                    $existing_data=array_combine($ids,$existing_data);
                }
                foreach($data as $record)
                {
                    $update=array();
                    if(isset($existing_data[$record['id']]))
                    {
                        $old_data=$existing_data[$record['id']];
                        foreach ($record as $key => $value)
                        {
                            if(array_key_exists($key,$old_data)&&$old_data[$key]!=$value)
                            {
                                $update[$key]=array('old_data'=>$old_data[$key],'new_data'=>$value);
                            }
                        }
                        if(!empty($update))
                        {
                            $edited_data_log[]=array('user_id'=>$_SESSION['user_data']['id'],'data'=>json_encode($update),'date_time'=>date('Y-m-d H:i:s'));

                        }
                    }
                }

            }
            if(empty($use_index))
            {
                $builder = $this->db->table($table);
                $builder->insertBatch($data,$id);
                $affected=$this->db->affectedRows();
            }
            else
            {
                $builder = $this->generate_update_query($table,$data,$id);

            }
        }
    }

}
