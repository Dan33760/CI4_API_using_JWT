<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\BoutiqueModel;
use App\Models\UserBoutiqueModel;
use App\Models\ProduitModel;
use App\Models\PanierModel;
use App\Models\PanierProduitModel;
use App\Models\UserModel;



class ClientController extends ResourceController
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
        $current_user = $this->userPayload()->id;
        $data=[];
        $boutiqueModel = new BoutiqueModel();

        $data['boutiques'] = $boutiqueModel->get_all();

        return $this->getResponse($data, ResponseInterface::HTTP_OK);

    }

    //enregistrement d'un client par un tenant
    public function client_add($id_store)
    {
        helper(['helper']);

        if($this->request->getPost())
        {
            $rules = [
                'role' => 'required',
                'nom' => 'required|min_length[3]|max_length[50]',
                'postnom' => 'required|max_length[50]',
                'email' => 'required|min_length[3]|max_length[50]|valid_email',
                'mdp' => 'required|max_length[50]'
            ];
            $input = $this->getRequestInput($this->request);

            if(!$this->validateRequest($input, $rules))
            {
                return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
            }else{

                $client = [
                    'REF_ROLE_USER' => $this->request->getVar('role'),
                    'NOM_USER' => $this->request->getVar('nom'),
                    'POSTNOM_USER' => $this->request->getVar('postnom'),
                    'EMAIL_USER' => $this->request->getVar('email'),
                    'MDP_USER' => $this->request->getVar('mdp'),
                    // 'IMAGE_USER' => 'uploads/clients/'.$image->getClientName()
                ];

                $clientModel = new UserModel();
                $user_boutique = new UserBoutiqueModel();

                $save = $clientModel->insert($client);

                $relation_U_B = [
                    'REF_BOUTIQUE' => $id_store,
                    'REF_USER' => $save
                ];
                $saveRelation = $user_boutique->save_u_b($relation_U_B);

                $response = ['message' => 'Client creer avec success'];
                return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
            }
        }
    }

    //== Activation et desactivation du compte client par un tenant
    public function client_active($store, $id)
    {
        $state = null;
        $clientModel = new UserModel();
        $client = $clientModel->find($id);

        if($client['ETAT_USER'] == 1) {
            $state = 2;
        }
        if($client['ETAT_USER'] == 2) {
            $state = 1;
        }
        $data = ['ETAT_USER' => $state];

        $active = $clientModel->update($id, $data);

        $response = ['message' => 'Etat client modifie avec success'];
        return $this->getResponse($response, ResponseInterface::HTTP_OK);
    }

    //== Suppression d'un client par un tenant (softDelete)
    public function client_delete($store, $id)
    {
        $clientModel = new UserModel();
        $data = ['ETAT_USER' => 0];
        $active = $clientModel->update($id, $data);

        $response = ['message' => 'Client supprime avec success'];
        return $this->getResponse($response, ResponseInterface::HTTP_OK);
    }

    //== Inscription d'un client a une boutique
    public function add_boutique($id_store)
    {
        $id_user = $this->userPayload()->id;
        $user_role = $this->userPayload()->role;
        $user_boutique = new UserBoutiqueModel();

        $boutique = $user_boutique->get_by_user($id_user, $user_role);

        foreach($boutique as $row)
        {
            if($row['REF_BOUTIQUE'] == $id_store){
                $response = ['error' => 'Vous etes membre de cette boutique'];
                return $this->getResponse($response, ResponseInterface::HTTP_OK);
            }
        }

        $relation_U_B = [
            'REF_BOUTIQUE' => $id_store,
            'REF_USER' => $id_user
        ];

        $saveRelation = $user_boutique->save_u_b($relation_U_B);
        $response = ['Message' => 'Boutique ajoute'];
        return $this->getResponse($response, ResponseInterface::HTTP_OK);
    }

    //== lister les produit d'une boutique
    public function view_produit($id_store)
    {
        $data = [];
        $produitModel = new ProduitModel();

        $data['produits'] = $produitModel->get_by_store($id_store);

        return $this->getResponse($data, ResponseInterface::HTTP_OK);


    }

    //== Liste des boutique d'un client
    public function boutique()
    {
        $current_user = $this->userPayload()->id;
        $user_role = $this->userPayload()->role;
        $data = [];
        $boutiqueModel = new BoutiqueModel();
        
        $data['boutiques'] = $boutiqueModel->get_by_user($current_user, $user_role);

        return $this->getResponse($data, ResponseInterface::HTTP_OK);
        
    }

    // Liste des produits et Ajout d'un panier
    public function view_produit_client($id_store)
    {
        $current_user = $this->userPayload()->id;
        $data = [];
        $produitModel = new Produitmodel();

        if($this->request->getPost())
        {
            $rules = [
                'designation' => 'required|min_length[3]|max_length[50]',
                'id_produit' => 'required',
                'pu_produit' => 'required',
                'qu_produit' => 'required',
            ];
            $input = $this->getRequestInput($this->request);

            if(!$this->validateRequest($input, $rules))
            {
                return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
            }else{
                $panier = [
                    'REF_USER_PANIER' => $this->userPayload()->id,
                    'DESIGNATION_PANIER' => $this->request->getVar('designation'),
                ];

                $panierModel = new PanierModel();
                $save_panier = $panierModel->insert($panier);

                for($i = 0; $i < count($this->request->getVar('id_produit')); $i++)
                {
                    $panier_produit[] = [
                        'REF_PANIER' => $save_panier,
                        'REF_PRODUIT' => (int) $this->request->getVar('id_produit')[$i],
                        'PU_PANIER' => (int) $this->request->getVar('pu_produit')[$i],
                        'QUANTITE_PRODUIT_PANIER' => (int) $this->request->getVar('qu_produit')[$i],
                        'PT_PANIER' => (int) $this->request->getVar('qu_produit')[$i] * (int) $this->request->getVar('pu_produit')[$i]
                    ];
                }

                $panierProduitModel = new PanierProduitModel();
                $save_panier_produit = $panierProduitModel->insertBatch($panier_produit);

                $response = ['Message' => 'Panier ajoute'];
                return $this->getResponse($response, ResponseInterface::HTTP_OK);

            }
        }

        $data['produits'] = $produitModel->get_by_store($id_store);
        return $this->getResponse($data, ResponseInterface::HTTP_OK);
    }
}
