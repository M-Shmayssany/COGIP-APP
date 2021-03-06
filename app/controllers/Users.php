<?php


    class Users extends Controller
    {
        public function __construct()
        {
            $this->userModel = $this->model('User');
            $this->typeModel = $this->model('Type');
        }

        public function register()
        {
            // Check for privileges
            if (isset($_SESSION['user_type']) && $_SESSION['user_type'] != '1')
            {
                redirect('');
            }

            // Get foreign key IDs
            $users_type = $this->typeModel->getUsersTypes();

            // Check for POST
            if ($_SERVER['REQUEST_METHOD'] == 'POST')
            {
                // Process form

                // Sanitize POST data
                $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

                // Init data
                $data =
                [
                    'name' => trim($_POST['name']),
                    'password' => trim($_POST['password']),
                    'confirm_password' => trim($_POST['confirm_password']),
                    'user_type_id' => trim($_POST['user_type_id']),
                    'users_type' => $users_type,
                    'name_err' => '',
                    'email_err' => '',
                    'password_err' => '',
                    'password_confirm_err' => ''
                ];

                // Validate Name
                if (empty($data['name']))
                {
                    $data['name_err'] = 'Please enter name';
                }
                else
                {
                    if ($this->userModel->findUserByName($data['name']))
                    {
                        $data['name_err'] = 'Name already taken';
                    }
                }

                // Validate Password
                if (empty($data['password']))
                {
                    $data['password_err'] = 'Please enter password';
                }
                elseif (strlen($data['password']) < 4)
                {
                    $data['password_err'] = 'Password must be at least 4 characters';
                }

                // Validate Confirm Password
                if (empty($data['confirm_password']))
                {
                    $data['confirm_password_err'] = 'Please confirm password';
                }
                else
                {
                    if ($data['password'] != $data['confirm_password'])
                    {
                        $data['confirm_password_err'] = 'Passwords do not match';
                    }
                }

                // Make sure errors are empty
                if (empty($data['name_err']) && empty($data['password_err']) && empty($data['confirm_password_err']))
                {
                    // Validated
                    
                    // Hash Password
                    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                    // Add User
                    if ($this->userModel->add($data))
                    {
                        flash('admin_message', 'User added');
                        redirect('users/login');
                    }
                    else
                    {
                        die('Something went wrong');
                    }
                }
                else
                {
                    // Load view with errors
                    $this->view('users/add', $data);
                }

            }
            else
            {
                // Init data
                $data =
                [
                    'name' => '',
                    'password' => '',
                    'confirm_password' => '',
                    'user_type_id' => '',
                    'users_type' => $users_type,
                    'name_err' => '',
                    'email_err' => '',
                    'password_err' => '',
                    'password_confirm_err' => ''
                ];

                // Load view
                $this->view('users/add', $data);
            }
        }

        public function login()
        {
            // Check for POST
            if ($_SERVER['REQUEST_METHOD'] == 'POST')
            {
                // Process form

                // Sanitize POST data
                $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

                // Init data
                $data =
                [
                    'name' => trim($_POST['name']),
                    'password' => trim($_POST['password']),
                    'name_err' => '',
                    'password_err' => ''
                ];

                // Validate Name
                if (empty($data['name']))
                {
                    $data['name_err'] = 'Please enter name';
                }

                // Validate Password
                if (empty($data['password']))
                {
                    $data['password_err'] = 'Please enter password';
                }

                // Check for user/name
                if ($this->userModel->findUserByName($data['name']))
                {
                    // User found
                }
                else
                {
                    // User not found
                    $data['name_err'] = 'No user found';
                }

                // Make sure errors are empty
                if (empty($data['name_err']) && empty($data['password_err']))
                {
                    // Validated
                    
                    // Check and set logged in user
                    $loggedInUser = $this->userModel->login($data['name'], $data['password']);

                    if ($loggedInUser)
                    {
                        // Create Session
                        $this->createUserSession($loggedInUser);
                    }
                    else
                    {
                        $data['password_err'] = 'Password incorrect';

                        $this->view('users/login', $data);
                    }
                }
                else
                {
                    // Load view with errors
                    $this->view('users/login', $data);
                }
            }
            else
            {
                // Init data
                $data =
                [
                    'name' => '',
                    'password' => '',
                    'name_err' => '',
                    'password_err' => ''
                ];

                // Load view
                $this->view('users/login', $data);
            }
        }

        public function createUserSession($user)
        {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_type'] = $user->type_id;
            $_SESSION['user_name'] = $user->name;
            redirect('admin/index');
        }

        public function logout()
        {
            unset($_SESSION['user_id']);
            unset($_SESSION['user_type']);
            unset($_SESSION['user_name']);
            session_destroy();
            redirect('users/login');
        }
    }

class Users extends Controller {
    public function __construct(){
        $this->userModel = $this->model('User');
    }

    public function register(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // process form


            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);

            // initialize data
            $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'password_confirm' => trim($_POST['password_confirm']),
                'name_err' => '',
                'email_err' => '',
                'password_err' => '',
                'password_confirm_err' => ''
            ];

            //Validate Email
            if(empty($data['email'])){
                $data['email_err'] = 'Please enter email';
            }else{
                //check email
                if($this->userModel->findUserByEmail($data['email'])){
                    $data['email_err'] = 'Email is already taken';
                }
            }

            //Validate name
            if(empty($data['name'])){
                $data['name_err'] = 'Please enter name';
            }

            //Validate password
            if(empty($data['password'])){
                $data['password_err'] = 'Please enter password';
            }elseif(strlen($data['password']) < 6){
                $data['password_err'] = 'Password must be at lest 6  characters';
            }


            //Validate password confirm
            if(empty($data['password_confirm'])){
                $data['password_confirm_err'] = 'Please confirm password';
            }else{
                if($data['password'] != $data['password_confirm']){
                    $data['password_confirm_err'] = 'Passwords do not match';
                }
            }

            // Make sure errors are empty
            if(empty($data['email_err']) && empty($data['name_err']) && empty($data['password_err']) && empty($data['password_confirm_err'])){
                //validated
                //Password Hash
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                //Register
                if($this->userModel->register($data)){
                    flash('register_success', 'You are registered and can log in.');
                    redirect('users/login');
                }else{
                    die('Something went wrong');
                }

            }else{
                //Load view with errors
                $this->view('users/register', $data);
            }

        }else{
            // initialize data
            $data = [
                'name' => '',
                'email' => '',
                'password' => '',
                'password_confirm' => '',
                'name_err' => '',
                'email_err' => '',
                'password_err' => '',
                'password_confirm_err' => ''
            ];
            
            // load view
            $this->view('users/register', $data);

        }
    }
    public function login(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // process form

            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);

            // initialize data
            $data = [
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'email_err' => '',
                'password_err' => ''
            ];

            //Validate Email
            if(empty($data['email'])){
                $data['email_err'] = 'Please enter email';
            }

            //Validate password
            if(empty($data['password'])){
                $data['password_err'] = 'Please enter password';
            }

            //check for user/email
            if($this->userModel->findUserByEmail($data['email'])){
                // User found
            }else{
                // User not found
                $data['email_err'] = 'no user found';
            }

            if(empty($data['email_err']) && empty($data['password_err'])){
                //validated
                //Check and set logged in user
                $loggedInUser = $this->userModel->login($data['email'], $data['password']);

                if($loggedInUser){
                    //Create session
                    $this->createUserSession($loggedInUser);
                }else{
                    $data['password_err'] = 'Password incorrect';

                    $this->view('users/login', $data);
                }
            }else{
                //Load view with errors
                $this->view('users/login', $data);
            }


        }else{
            // initialize data
            $data = [
                'email' => '',
                'password' => '',
                'email_err' => '',
                'password_err' => '',
            ];
        // load view
            $this->view('users/login', $data);
        }
    
    }
    

    public function createUserSession($user){
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->name;
        redirect('pages/index');
    }

    public function logout(){
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);
        session_destroy();
        redirect('users/login');
    }

    public function isLoggedIn(){
        if(isset($_SESSION['user_id'])){
            return true;
        }else{
            return false;
        }
    }
}
>>>>>>> origin/mohamed
