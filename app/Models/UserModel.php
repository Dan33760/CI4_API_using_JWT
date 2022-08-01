<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'users';
    protected $primaryKey       = 'ID_USER';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['REF_ROLE_USER', 'NOM_USER', 'POSTNOM_USER', 'EMAIL_USER', 'MDP_USER', 'IMAGE_USER', 'DATE_ENR_USER', 'ETAT_USER'];

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
    protected $beforeInsert   = ['hashPasswordInsert'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['hashPasswordUpdate'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // Hasher le mot de passe avant insertion
    protected function hashPasswordInsert(array $data)
    {
        $data = $this->passwordhash($data);
        return $data;
    }

    // Hasher le mot de passe avant modification
    protected function hashPasswordUpdate(array $data)
    {
        $data = $this->passwordhash($data);
        return $data;
    }

    // Fonction pour hasher le mot de passe
    protected function passwordhash(array $data)
    {
        if(isset($data['data']['MDP_USER']))
            $data['data']['MDP_USER'] = password_hash($data['data']['MDP_USER'], PASSWORD_DEFAULT);
        return $data;
    }

    // Recuperer un utilisateur par son addrese mail
    public function get_all($email)
    {
        $where = ['EMAIL_USER' => $email, 'ETAT_USER' => 1];

        $builder = $this->db->table('users');
        $builder->select('*');
        $builder->where($where);
        $builder->join('roles', 'roles.ID_ROLE = users.REF_ROLE_USER');
        $query = $builder->get();
        return $query->getRow();
    }

    // == Recupere tous les utilisateurs actifs ====
    public function get_users()
    {
        $userModel = new UserModel();
        return $users = $userModel->where('ETAT_USER >=', 1)->findAll();
    }

    //== recupere tous les utilisateur
    public function get_all_users()
    {
        $builder = $this->db->table('users');
        $builder->select('*');
        $builder->join('roles', 'roles.ID_ROLE = users.REF_ROLE_USER');
        $query = $builder->get();
        return $query->getResult();
    }

    //== fonction  pour compter les clients d'une boutique
    public function get_count($id_store)
    {
        $builder = $this->db->table('users');
        $builder->select('*');
        $builder->where('ETAT_USER >=',1);
        $builder->where('REF_BOUTIQUE_PRODUIT =',$id_store);
        return $count = $builder->countAllResults();
    }

    //== Recupere les utilisateur d'une boutique
    public function get_by_store($id_store)
    {
        $builder = $this->db->table('users');
        $builder->select('*');
        $builder->where('ETAT_USER >=', 1);
        $builder->where('user_boutique.REF_BOUTIQUE', $id_store);
        $builder->where('roles.DESIGNATION_ROLE', "client");
        $builder->join('roles', 'roles.ID_ROLE = users.REF_ROLE_USER');
        $builder->join('user_boutique', 'user_boutique.REF_USER = users.ID_USER');
        $builder->join('boutiques', 'boutiques.ID_BOUTIQUE = user_boutique.REF_BOUTIQUE');
        $query = $builder->get();
        return $query->getResult();
    }

    //== Nombre des client par boutique
    public function count_client($id_store)
    {
        $builder = $this->db->table('users');
        $builder->select('*');
        $builder->where('ETAT_USER >=', 1);
        $builder->where('user_boutique.REF_BOUTIQUE', $id_store);
        $builder->where('roles.DESIGNATION_ROLE', "client");
        $builder->join('roles', 'roles.ID_ROLE = users.REF_ROLE_USER');
        $builder->join('user_boutique', 'user_boutique.REF_USER = users.ID_USER');
        $builder->join('boutiques', 'boutiques.ID_BOUTIQUE = user_boutique.REF_BOUTIQUE');
        $query = $builder->countAllResults();
        return $query;
    }

    public function soft_delete($id_user)
    {
        $model = new UserModel();
        $delete = $model->update($id_user, ['ETAT_USER' => 0]);
        return $delete;
    }

    public function findUserByEmail(string $emailAddess)
    {
        $user = $this->where(['EMAIL_USER', $emailAddess])->first();
        
        if(!empty($user))
        {
            throw new Exception('User does not exist for specified email address');
        }
        
        return $user;
    }

}
