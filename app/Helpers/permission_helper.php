<?php
function controller_permissions()
{
    //Users
    $permissions['users']['index']='view users';
    $permissions['users']['view_user']='view users';
    $permissions['users']['edit_user']='edit users';
    $permissions['users']['new_user']='add users';
    $permissions['users']['reset_password']='reset user password';
    $permissions['users']['change_password']='change password';
    $permissions['users']['change_theme']='change theme';

    //Roles
    $permissions['roles']['index']='view user roles';
    $permissions['roles']['view_role']='view user roles';
    $permissions['roles']['edit_role']='edit user roles';
    $permissions['roles']['new_role']='add user role';

    //Edited data log
    $permissions['edited_data_log']['index']='view edited data log';
    $permissions['edited_data_log']['view_log']='view edited data log';

    //Activity data log
    $permissions['activity_log']['index']='view activity log';
    $permissions['activity_log']['view_log']='view activity log';

    //Countries
    $permissions['countries']['index']='view countries';
    $permissions['countries']['view_country']='view countries';
    $permissions['countries']['edit_country']='edit country';
    $permissions['countries']['new_country']='add country';

    //Apps
    $permissions['apps']['index']='view apps';
    $permissions['apps']['view_app']='view apps';
    $permissions['apps']['edit_app']='edit app';
    $permissions['apps']['new_app']='add app';
    $permissions['apps']['reset_password']='reset app password';

    //Wallets
    $permissions['wallets']['index']='view wallets';
    $permissions['wallets']['view_wallet']='view wallets';
    $permissions['wallets']['edit_wallet']='edit wallet';
    $permissions['wallets']['new_wallet']='add wallet';

    //Logged Numbers
    $permissions['logged_numbers']['index']='view logged numbers';
    $permissions['logged_numbers']['view_logged_number']='view logged numbers';
    $permissions['logged_numbers']['edit_logged_number']='edit logged number';
    $permissions['logged_numbers']['new_logged_number']='add logged number';

    //RPC
    $permissions['rpc']['postdelete_selected_logged_numbers']='delete logged number';
    $permissions['rpc']['postactivate_selected_wallets']='edit wallet';
    $permissions['rpc']['postdeactivate_selected_wallets']='edit wallet';
    $permissions['rpc']['postactivate_collection_for_selected_wallets']='edit wallet';
    $permissions['rpc']['postdeactivate_collection_for_selected_wallets']='edit wallet';
    $permissions['rpc']['postactivate_disbursement_for_selected_wallets']='edit wallet';
    $permissions['rpc']['postdeactivate_disbursement_for_selected_wallets']='edit wallet';
    $permissions['rpc']['postdelete_selected_currencies']='delete currency';
    $permissions['rpc']['postdelete_selected_prefixes']='delete network prefix';
    $permissions['rpc']['postdelete_selected_ip_pools']='delete user IP Pool';
    $permissions['rpc']['postactivate_selected_wallet_libraries']='edit wallet library';
    $permissions['rpc']['postdeactivate_selected_wallet_libraries']='edit wallet library';
    $permissions['rpc']['postactivate_logging_for_selected_wallet_libraries']='edit wallet library';
    $permissions['rpc']['postdeactivate_logging_for_selected_wallet_libraries']='edit wallet library';
    $permissions['rpc']['postactivate_selected_transaction_accounts']='edit transaction account';
    $permissions['rpc']['postdeactivate_selected_transaction_accounts']='edit transaction account';
    $permissions['rpc']['postdelete_selected_transaction_parameters']='delete transaction parameter';

    //Supported Currencies
    $permissions['supported_currencies']['index']='view currencies';
    $permissions['supported_currencies']['view_currency']='view currencies';
    $permissions['supported_currencies']['edit_currency']='edit currency';
    $permissions['supported_currencies']['new_currency']='add currency';

    //Mobile Network Prefixes
    $permissions['mobile_network_prefixes']['index']='view network prefixes';
    $permissions['mobile_network_prefixes']['view_prefix']='view network prefixes';
    $permissions['mobile_network_prefixes']['edit_prefix']='edit network prefix';
    $permissions['mobile_network_prefixes']['new_prefix']='add network prefix';

    //Whitelisted User IPs
    $permissions['whitelisted_user_ips']['index']='view user IP Pools';
    $permissions['whitelisted_user_ips']['view_pool']='view user IP Pools';
    $permissions['whitelisted_user_ips']['edit_pool']='edit user IP Pool';
    $permissions['whitelisted_user_ips']['new_pool']='add user IP Pool';

    //Wallet Libraries
    $permissions['wallet_libraries']['index']='view wallet libraries';
    $permissions['Wallet_libraries']['view_library']='view wallet libraries';
    $permissions['Wallet_libraries']['edit_library']='edit wallet library';
    $permissions['Wallet_libraries']['new_library']='add wallet library';

    //Transaction Accounts
    $permissions['transaction_accounts']['index']='view transaction accounts';
    $permissions['transaction_accounts']['view_account']='view transaction accounts';
    $permissions['transaction_accounts']['edit_account']='edit transaction account';
    $permissions['transaction_accounts']['new_account']='add transaction account';

    //Transaction Parameters
    $permissions['transaction_parameters']['index']='view transaction parameters';
    $permissions['transaction_parameters']['view_parameter']='view transaction parameters';
    $permissions['transaction_parameters']['edit_parameter']='edit transaction parameter';
    $permissions['transaction_parameters']['new_parameter']='add transaction parameter';

    //Transaction Request log
    $permissions['transaction_request_log']['index']='view transaction request log';
    $permissions['transaction_request_log']['view_log']='view transaction request log';

    return $permissions;
}
function other_permissions()
{
    $permissions[]='change user role';
    $permissions[]='assign admin role';
    return $permissions;
}
function get_method_permissions($controller,$method)
{
    $controller=strtolower($controller);
    $method=strtolower($method);
    $permissions=controller_permissions();
    if(isset($permissions[$controller][$method]))
    {
        return $permissions[$controller][$method];
    }
    return array();
}
function get_permissions()
{
    $controller_permissions=controller_permissions();
    $permissions=other_permissions();

    foreach ($controller_permissions as $controller=>$method_permissions)
    {
        foreach ($method_permissions as $method_permissions)
        {
            if(!is_array($method_permissions))
            {
                $permissions[]=$method_permissions;
                continue;
            }
            foreach ($method_permissions as $method_permission)
            {
                $permissions[]=$method_permission;
            }
        }
    }
    $permissions=array_unique($permissions);
    sort($permissions);
    return $permissions;
}
function user_has_access($controller,$method)
{
    $permissions=get_method_permissions($controller,$method);
    if(empty($permissions))
    {
        return true;
    }
    return user_has_permission($permissions);
}
function user_has_permission($permission)
{
    if(!isset($_SESSION['permissions'])) return false;
    if(!is_array($permission))
    {
        $permission=array($permission);
    }
    foreach($permission as $p)
    {
        if(in_array($p,$_SESSION['permissions'])) return true;
    }
    return false;
}
