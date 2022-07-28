<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\PanierModel;
use App\Models\ProduitModel;
use App\Models\PanierProduitModel;

class PanierController extends ResourceController
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
        //
    }

     // Ajout d'un panier
     public function add_panier($id_store)
     {
         $data = [];
         $produitModel = new Produitmodel();
    
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

    // Liste des panier d'un client dans une boutique
    public function panier_client($id_store)
    {
        $current_user[] = $this->userPayload()->id;
        $data = [];
        $panierModel = new PanierModel();
        $data['paniers'] = $panierModel->get_by_user($current_user, $id_store);

        return $this->getResponse($data, ResponseInterface::HTTP_OK);

    }

    //== Voir les details et modifier un panier
    public function panier_detail($id_panier)
    {
        $current_user = $this->userPayload()->id;
        $data = [];
        $produitModel = new ProduitModel();
        $panierModel = new PanierModel();

        if($this->request->getPost())
        {
            $rules = [
                'designation' => 'required|min_length[3]|max_length[50]',
                'id_produit' => 'required',
                'pu_produit' => 'required',
                'qu_produit' => 'required',
            ];
            if(!$this->validate($rules))
            {
                $data['validation'] = $this->validator;
            }else{
                $panier = [
                    'DESIGNATION_PANIER' => $this->request->getVar('designation'),
                ];

                $save_panier = $panierModel->update($id_panier,$panier);
                $panierProduitModel = new PanierProduitModel();

                for($i = 0; $i < count($this->request->getVar('id_produit')); $i++)
                {
                    $id_produit = $this->request->getVar('id_produit')[$i];
                    
                    $pu_produit = $this->request->getVar('pu_produit')[$i];
                    $qu_produit = $this->request->getVar('qu_produit')[$i];
                    $tot_produit = $this->request->getVar('qu_produit')[$i] * $this->request->getVar('pu_produit')[$i];

                    $save_panier_produit = $panierProduitModel->update_produit($id_panier,$id_produit,$pu_produit,$qu_produit,$tot_produit);
                }

                if(!$save_panier_produit){
                    $data['success'] = "Panier modifier";
                }else{
                    $data['error'] = "Panier non modifier";
                }

                return $this->getResponse($data, ResponseInterface::HTTP_OK);
            }
        }

        $data['panier'] = $panierModel->get_one($id_panier);
        $data['produits'] = $produitModel->get_by_panier($current_user, $id_panier);

        return $this->getResponse($data, ResponseInterface::HTTP_OK);
    }

    //== Supprimer un produit du panier
    public function panier_delete_produit($id_panier,$id_produit)
    {
        $panierProduitModel = new PanierProduitmodel();
        $delete = $panierProduitModel->delete_produit($id_panier, $id_produit);
        
        $response = ['message' => 'Produit retiré du panier'];
        return $this->getResponse($response, ResponseInterface::HTTP_OK);
    }

    //== Valider un panier
    public function valider_panier($id_panier)
    {
        $panierModel = new PanierModel();
        $valider = $panierModel->validate_panier($id_panier);

        $response = ['message' => 'Panier validé'];
        return $this->getResponse($response, ResponseInterface::HTTP_OK);
    }

    // Liste des panier d'un client
    public function panier()
    {
        $current_user = $this->userPayload()->id;
        $panierModel = new PanierModel();
        $data = [];

        $data['paniers'] = $panierModel->getAll_by_user($current_user);

        return $this->getResponse($data, ResponseInterface::HTTP_OK);

    }
}
