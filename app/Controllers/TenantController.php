<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\ProduitModel;
use App\Models\UserModel;
use App\Models\UserBoutiqueModel;
use App\Models\BoutiqueModel;


class TenantController extends ResourceController
{
    use ResponseTrait;

    // ---- payload data from request ----------
    public function userPayload()
    {
        helper('jwt');
        $authenticationHeader = $this->request->getServer('HTTP_AUTHORIZATION');
        return $decodedToken = getUserPayload($authenticationHeader);
    }

    // liste des boutiques d'un tenant
    public function index()
    {
        $boutiqueModel = new BoutiqueModel();
        $current_user = $this->userPayload()->id;

        $data['count_boutique'] = $boutiqueModel->count_by_user($current_user);

        return $this->getReaponse($data, ResponseInterface::HTTP_OK);

    }

    public function client()
    {
        echo view('tenant/client_list');
    }
}
