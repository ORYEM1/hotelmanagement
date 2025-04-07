<?php
namespace App\Controllers;

use App\Models\BaseModel;

class Login extends BaseController
{

    protected BaseModel $base_model;

    public function __construct()
    {
        $this->base_model = new BaseModel(); // Initialize the model here
    }
    public function index()
    {
        helper(['my_form']); // Ensure this helper has nothing breaking
        $vars = [];
        //print_r($this->request->getMethod()); exit();

        if ($this->request->getMethod() == 'POST') {
            $validation = \Config\Services::validation();

            $validation->setRules([
                'username' => 'required',
                'password' => 'required|min_length[8]'
            ]);

            if ($validation->withRequest($this->request)->run()) {
                $username = $this->request->getPost('username');
                $password = $this->request->getPost('password');

                $login = $this->process_login($username, $password);

                if (isset($login['error'])) {
                    $vars['error'] = $login['error'];
                } elseif (isset($login['success'])) {
                    return redirect()->to(base_url('reservation')); // Your success redirect
                }
            } else {
                $vars['error'] = $validation->listErrors();
            }
        }

        $vars['title'] = 'Login';
        return view('login/view_login', $vars);
    }

    private function process_login($username, $password): array
    {
        $user_data = $this->base_model->get_data([
            'table' => 'users',
            'where' => ['username' => $username]
        ], assoc: true);

        if (empty($user_data)) {
            return ['error' => 'Invalid username or password'];
        }

        if (!password_verify($password, $user_data['password'])) {
            return ['error' => 'Invalid username or password'];
        }

        if ($user_data['status'] !== 'active') {
            return ['error' => 'Your account is not active'];
        }

        $activity_log = [
            'user_id' => $user_data['id'],
            'activity' => 'Login'
        ];
        $this->base_model->insert_data('activity_log', $activity_log);

        session()->set('user_data', $user_data);

        return ['success' => true]; // Just return success
    }

}
