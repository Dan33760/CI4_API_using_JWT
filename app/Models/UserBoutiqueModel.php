<?php

namespace App\Models;

use CodeIgniter\Model;

class UserBoutiqueModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'user_boutique';
    protected $primaryKey       = ['REF_BOUTIQUE', 'REF_USER'];
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['REF_BOUTIQUE', 'REF_USER'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function save_u_b($relation)
    {
        $model = new UserBoutiqueModel();
        $save = $model->insert($relation);
        return $save;
    }

    public function get_by_user($id_user)
    {
        $model = new UserBoutiqueModel();
        $boutique = $model->where('REF_USER', $id_user)->findAll();
        return $boutique;
    }

    
}
