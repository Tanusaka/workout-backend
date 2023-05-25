<?php
/**
 *
 * @author Samu
 */
namespace App\Models\Core;

use CodeIgniter\Model;

class UserModel extends Model
{
  protected $table      = '_users';
  protected $primaryKey = 'id';

  public function getUsers($status='')
  {
    $selectColumns = ['id','authid','usertype','name','dob','gender','status'];

    if ($status=='') {
      return $this->select($selectColumns)->findAll();
    } else {
      return $this->select($selectColumns)->where('status', $status)->findAll();
    }    
  }

}