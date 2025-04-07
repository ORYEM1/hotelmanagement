<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;  // Correct import for LoggerInterface

class RestrictedBaseController extends Controller
{
    protected $request;
    protected $helpers = ['url', 'general', 'text', 'form', 'my_form', 'menu', 'data_tables', 'query', 'permission'];
    protected \App\Models\BaseModel $baseModel;
    protected \App\Libraries\AdvancedSearch $advancedSearch;
    protected \App\Libraries\Permission $permission;
    protected $session;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->baseModel = new \App\Models\BaseModel();
        $this->session = session();
        $this->advancedSearch = new \App\Libraries\AdvancedSearch();
        $this->permission = new \App\Libraries\Permission();

        helper($this->helpers);

        // Check if the user is logged in and has permission
        if (!$this->session->has('user_data') || !$this->session->has('permission')) {
            return redirect()->to(base_url('login'));
        }

        $this->refresh_user_data();
        $this->permission->refresh_permission();
        date_default_timezone_set($_ENV['locale.timezone']);
    }

    public function refresh_user_data()
    {
        if (!$this->session->has('user_data')) {
            return redirect()->to(base_url('login'));
        }

        $user_data = $this->baseModel->get_data([
            'table' => 'users',
            'where' => ['id' => $this->session->get('user_data')['id']],
            'use_cache' => true
        ], true);

        if (empty($user_data)) {
            return redirect()->to(base_url('login'));
        }

        if (strtolower($user_data['status']) !== '1') {
            return redirect()->to(base_url('login'));
        }

        $this->session->set('user_data', $user_data);
        return true;
    }

    public function set_search_data($params)
    {
        $this->advancedSearch->set_search_data($params);
    }
}
