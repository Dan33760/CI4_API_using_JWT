<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ProduitModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class ProduitController extends ResourceController
{
    use ResponseTrait;

    // ---- payload data from request ----------
    public function userPayload()
    {
        helper('jwt');
        $authenticationHeader = $this->request->getServer('HTTP_AUTHORIZATION');
        return $decodedToken = getUserPayload($authenticationHeader);
    }

    //Liste des produits
    public function index()
    {
        $data = [];
        $produitModel = new ProduitModel();
        
        $data['total'] = $produitModel->where('ETAT_PRODUIT >=', 1)->countAll();
        $data['produits'] = $produitModel->where('ETAT_PRODUIT >=', 1)->findAll();
        
        return $this->getResponse($data, ResponseInterface::HTTP_OK);
    }

    //== Function pour ajouter un nouveau produit
    public function produit_add($id)
    {
        helper(['form']);

        if($this->request->getPost())
        {
            $rules = [
                'designation' => 'required|min_length[3]|max_length[50]',
                'pu' => 'required|max_length[50]',
                'quantite' => 'required|max_length[50]',
                'marge' => 'required|max_length[50]',
                'image_produit' => "uploaded[image_produit]|max_size[image_produit,2048]|is_image[image_produit]|mime_in[image_produit,image/jpg,image/jpeg,image/png]",
            ];

            $input = $this->getRequestInput($this->request);

            if(!$this->validateRequest($input, $rules))
            {
                return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
            }else{
                $image = $this->request->getFile('image_produit');
                $image->move('uploads/produits');

                $produit = [
                    'REF_BOUTIQUE_PRODUIT' => $id,
                    'DESIGNATION_PRODUIT' => $this->request->getVar('designation'),
                    'PU_PRODUIT' => $this->request->getVar('pu'),
                    'QUANTITE_PRODUIT' => $this->request->getVar('quantite'),
                    'MARGE_PRODUIT' => $this->request->getVar('marge'),
                    'IMAGE_PRODUIT' => 'uploads/produits/'.$image->getClientName()
                ];

                $produitModel = new ProduitModel();
                $save = $produitModel->insert($produit);

                $response = ['message' => 'Produit creer avec success'];
                return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
            }
        }
        // return redirect()->to('/tenant/boutique_view/'.$id.'');
    }

    // Detail et modification d'un produit
    public function produit_edit($store, $id_produit)
    {
        $data = [];
        $produitModel = new ProduitModel();
       
        $rules = [
            'designation' => 'required|min_length[3]|max_length[50]',
            'pu' => 'required|max_length[50]',
            'quantite' => 'required|max_length[50]',
            'marge' => 'required|max_length[50]',
            'image_produit' => "uploaded[image_produit]|max_size[image_produit,2048]|is_image[image_produit]|mime_in[image_produit,image/jpg,image/jpeg,image/png]",
        ];

        $input = $this->getRequestInput($this->request);

        if(!$this->validateRequest($input, $rules))
        {
            return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }else{
            $produit = [];
            $imgName = [];

            if(!$this->request->getFile('image_produit')) {
                $image = $this->request->getFile('image_produit');
                $image->move('uploads/produits');
                $imgName = "dan";

                $produit = [
                    'DESIGNATION_PRODUIT' => $this->request->getVar('designation'),
                    'PU_PRODUIT' => (int) $this->request->getVar('pu'),
                    'QUANTITE_PRODUIT' => (int) $this->request->getVar('quantite'),
                    'MARGE_PRODUIT' => (int) $this->request->getVar('marge'),
                    'IMAGE_PRODUIT' => 'uploads/produits'.$image->getClientName()
                ];
            }else{
                $produit = [
                    'DESIGNATION_PRODUIT' => $this->request->getVar('designation'),
                    'PU_PRODUIT' => (int) $this->request->getVar('pu'),
                    'QUANTITE_PRODUIT' => (int) $this->request->getVar('quantite'),
                    'MARGE_PRODUIT' => (int) $this->request->getVar('marge'),
                ];
            }
            

            $produitModel = new ProduitModel();
            $save = $produitModel->update($id_produit, $produit);

            $response = ['message' => 'Produit modifie avec success'];
            return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
            
        }

        $data['produit'] = $produitModel->find($id_produit);

        return $this->getResponse($data, ResponseInterface::HTTP_CREATED);
    }

    // Activer et desactiver un produit
    public function produit_active($id)
    {
        $state = null;
        $produitModel = new ProduitModel();
        $produit = $produitModel->find($id);
        $response = [];

        if($produit['ETAT_PRODUIT'] == 1) {
            $state = 2;
            $response = ['message' => 'Produit desactivé'];
        }
        if($produit['ETAT_PRODUIT'] == 2) {
            $state = 1;
            $response = ['message' => 'Produit activé'];
        }
        $data = ['ETAT_PRODUIT' => $state];

        $active = $produitModel->update($id, $data);

        return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
    }

    // Suppression d'un produit
    public function produit_delete($id)
    {
        $produitModel = new ProduitModel();
        $data = ['ETAT_PRODUIT' => 0];
        $active = $produitModel->update($id, $data);

        $response = ['message' => 'Produit supprime avec success'];
        return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
    }
}
