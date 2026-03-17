<?php namespace App\Models;
use CodeIgniter\Model;

class ClassModel extends Model {
    protected $table            = 'classes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['id', 'name'];
}
