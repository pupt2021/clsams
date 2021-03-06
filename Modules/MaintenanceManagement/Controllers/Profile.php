<?php namespace Modules\MaintenanceManagement\Controllers;

use App\Controllers\BaseController;
use Modules\MaintenanceManagement\Models as MaintenanceManagement;
use Modules\MaintenanceManagement\Models\UsersModel;
use Modules\UserManagement\Models\RolesModel;

class Profile extends BaseController {

  function __construct(){
  }


  public function index()
  {
    helper(['form', 'url', 'html']);
    $id = $_SESSION['uid'];
    $model = new UsersModel();
    $roleModel = new RolesModel();
    $data['roles'] = $roleModel->getRoles();
    $data['rec'] = $model->find($id);

    // $encrypt_method = "AES-256-CBC";
    // $secret_key = 'AA74CDCC2BBRT935136HH7B63C27'; // user define private key
    // $secret_iv = '5fgf5HJ5g27'; // user define secret key
    // $key = hash('sha256', $secret_key);
    // $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
    // $output = openssl_decrypt(base64_decode($data['rec']['password']), $encrypt_method, $key, 0, $iv);
    // $data['rec']['password'] = $output;
    $data['view'] = 'Modules\MaintenanceManagement\Views\profile\frmUser';
    echo view('App\Views\template\index', $data);
  }

  function encrypt_decrypt($string, $action = 'encrypt')
  {
      $encrypt_method = "AES-256-CBC";
      $secret_key = 'AA74CDCC2BBRT935136HH7B63C27'; // user define private key
      $secret_iv = '5fgf5HJ5g27'; // user define secret key
      $key = hash('sha256', $secret_key);
      $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
      if ($action == 'encrypt') {
          $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
          $output = base64_encode($output);
      } else if ($action == 'decrypt') {
          $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
      }
      return $output;
  }
  public function edit($id){
    
    $model = new UsersModel();
    $roleModel = new RolesModel();
    $data['roles'] = $roleModel->getRoles();
    $data['rec'] = $model->getUsersWithRolesById($id);
    
    if(!empty($_POST))
    {
      
      if (!$this->validate('profile'))
      {
        $data['errors'] = \Config\Services::validation()->getErrors();
          $data['function_title'] = "Profile";
          $data['view'] = 'Modules\MaintenanceManagement\Views\profile\frmUser';
          echo view('App\Views\template\index', $data);
      }
      else
      {
        if(!empty($_FILES)){
          $targetDir = $_SERVER['DOCUMENT_ROOT'] ."/assets/uploads/".$data['rec']['id']."/";
          $fileName = basename($_FILES['profile_image']["name"]);
          $oldFilePath = $data['rec']['profile_image'];
          $targetFilePath = $targetDir .'/'. $fileName;
          $fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);
          $allowTypes = array('jpg','JPG','PNG','png','jpeg','JPEG');

          if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
          }
          if(!empty($data['rec']['profile_image'])){
            $dd = substr($oldFilePath, strlen(base_url()));
            $delete_file = $_SERVER['DOCUMENT_ROOT'].$dd;
            if (file_exists($delete_file)) {
              unlink($delete_file);
            } 
          }
          

            if(in_array($fileType, $allowTypes)){
              // Upload file to server
              if(move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFilePath)){
                $_POST['profile_image'] = base_url('/assets/uploads/'.$data['rec']['id'].'/'.$fileName);
                if($model->editUsers($_POST, $id))
                {
                  $_SESSION['success'] = 'You have updated a record';
                  $this->session->markAsFlashdata('success');
                  return redirect()->to(base_url('admin/profile'));
                }
                else
                {
                  $_SESSION['error'] = 'You an error in updating a record';
                  $this->session->markAsFlashdata('error');
                  return redirect()->to( base_url('admin/profile'));
                }
              }
              
            }else{
              $_SESSION['error'] = "Sorry, only JPG, JPEG, PNG, image are allowed to upload.";
              $this->session->markAsFlashdata('error');
              return redirect()->to( base_url('admin/profile'));
            }
        
      
         
        }else{
          if($model->editUsers($_POST, $id))
          {
            $_SESSION['success'] = 'You have updated a record';
            $this->session->markAsFlashdata('success');
            return redirect()->to(base_url('admin/profile'));
          }
          else
          {
            $_SESSION['error'] = 'You an error in updating a record';
            $this->session->markAsFlashdata('error');
            return redirect()->to( base_url('admin/profile'));
          }
        }
        
        
      }
    }
    else
    {
      $data['function_title'] = "Profile";
        $data['view'] = 'Modules\MaintenanceManagement\Views\profile\frmUser';
        echo view('App\Views\template\index', $data);
    }
  }
  }
