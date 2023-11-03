<?php
/**
 *
 * @author Samu
 */
namespace App\Models\Core;


use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class BaseModel extends Model
{
    public $currentuser = 'SYSTEM';
}