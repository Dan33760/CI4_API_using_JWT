<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\BoutiqueModel;
use App\Models\UserBoutiqueModel;
use App\Models\ProduitModel;
use App\Models\RoleModel;
use App\Models\UserModel;

class BoutiqueController extends ResourceController
{
    use ResponseTrait;

    // ---- payload data from request ----------
    public function userPayload()
    {
        helper('jwt'); 
        $authenticationHeader = $this->request->getServer('HTTP_AUTHORIZATION');
        return $decodedToken = getUserPayload($authenticationHeader);
    }

    public function index()
    {
        $data = [];
        $boutiqueModel = new BoutiqueModel();
        $id_user = $this->userPayload()->id;
        $user_role = $this->userPayload()->role;

        if($this->request->getPost())
        {
            $rules = [
                'designation' => 'required|min_length[3]|max_length[50]',
                'description' => 'required|min_length[3]'
            ];

            $input = $this->getRequestInput($this->request);

            if(!$this->validateRequest($input, $rules))
            {
                return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
            }else{
                $boutiqueModel = new BoutiqueModel();
                $user_boutique = new UserBoutiqueModel();

                $boutique = [
                    'DESIGNATION_BOUTIQUE' => $this->request->getVar('designation'),
                    'DESCRIPTION_BOUTIQUE' => $this->request->getVar('description'),
                ];
                $saveBoutique = $boutiqueModel->save_boutique($boutique);

                $relation_U_B = [
                    'REF_BOUTIQUE' => $saveBoutique,
                    'REF_USER' => $this->userPayload()->id
                ];
                $saveRelation = $user_boutique->save_u_b($relation_U_B);

                $response = ['message' => 'Boutique creer avec success'];
                return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
            }
        }
        $data['boutiques'] = $boutiqueModel->get_by_user($id_user, $user_role);

        return $this->getResponse($data, ResponseInterface::HTTP_OK);
    }

    // Modifier une boutique
    public function boutique_edit($id)
    {
        $data = [];
        $boutiqueModel = new BoutiqueModel();

        $rules = [
            'designation' => 'required|min_length[3]|max_length[50]',
            'description' => 'required|min_length[3]'
        ];

        $input = $this->getRequestInput($this->request);

        if(!$this->validateRequest($input, $rules))
        {
            return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }else{
            $boutiqueModel = new BoutiqueModel();

            $boutique = [
                'DESIGNATION_BOUTIQUE' => $this->request->getVar('designation'),
                'DESCRIPTION_BOUTIQUE' => $this->request->getVar('description'),
            ];
            $update = $boutiqueModel->update_boutique($id, $boutique);

            $response = ['message' => 'Boutique modifiee avec success'];
            return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
        }
    }

    //== activer ou desactiver une boutique par un tenant
    // public function boutique_active($id)
    // {
    //     $this->active_store($id);
    //     $response = ['message' => 'Etat boutique modifié'];
    //     return $this->getResponse($response, ResponseInterface::HTTP_OK);
    // }

    // //== activer ou desactiver une boutique par un tenant
    // public function boutique_active_admin($id_tenant,$id_store)
    // {
    //     $this->active_store($id_store);
    //     $response = ['message' => 'Etat boutique modifié'];
    //     return $this->getResponse($response, ResponseInterface::HTTP_OK);
    // }

    //== Activation et desactivation d'une boutique 
    public function active_store($id)
    {
        $state = null;
        $boutiqueModel = new BoutiqueModel();
        $boutique = $boutiqueModel->get_one($id);

        if($boutique['ETAT_BOUTIQUE'] == 1) {
            $state = 2;
            $response = ['message' => 'Boutique activé'];
        }
        if($boutique['ETAT_BOUTIQUE'] == 2) {
            $state = 1;
            $response = ['message' => 'Boutique desactivé'];
        }
        $data = ['ETAT_BOUTIQUE' => $state];
        $active = $boutiqueModel->update_boutique($id, $data);
        
        return $this->getResponse($response, ResponseInterface::HTTP_OK);
    }

    //== supprimer par le tenant
    public function boutique_delete($id)
    {
        $this->delete($id);

        $response = ['message' => 'Boutique supprimeé'];
        return $this->getResponse($response, ResponseInterface::HTTP_OK);
    }

    //== Supprimer par un admin
    public function boutique_delete_admin($id_tenant,$id)
    {
        $this->delete($id);

        $response = ['message' => 'Boutique supprimeé'];
        return $this->getResponse($response, ResponseInterface::HTTP_OK);

    }

    //== fonction de Suppression d'une boutique
    public function deleteBoutique($id)
    {
        $boutiqueModel = new BoutiqueModel();
        $data = ['ETAT_BOUTIQUE' => 0];
        $delete = $boutiqueModel->update_boutique($id, $data);
        
        return $this->getResponse(['message' => 'Boutique Supprimer'], ResponseInterface::HTTP_OK);

    }

    // Details d'une boutique pour un tenant
    public function boutique_view($id)
    {
        $roleModel = new RoleModel;
        $produitModel = new ProduitModel();
        $userModel = new UserModel();

        $data = [];
        $data['id'] = $id;

        $data['roles'] = $roleModel->get_role();

        $data['produits'] = $produitModel->get_by_store($id);
        $data['count_produit'] = $produitModel->get_count($id);

        $data['clients'] = $userModel->get_by_store($id);
        $data['count_client'] = $userModel->count_client($id);

        return $this->getResponse($data, ResponseInterface::HTTP_OK);
    }

}
