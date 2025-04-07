<?php

namespace App\Controllers;

class Test extends BaseController
{


    public function index()
    {
         $gw_functions=get_gateway_functions('UG_Iotec_Pay_API');
         print_r($gw_functions); exit;
    }
}
