<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use App\Models\UserModel;
use App\Models\RoleModel;

// use \Firebase\JWT\JWT;
use Exception;
use ReflectionException;

class UserController extends ResourceController
{
    use ResponseTrait;

    // ---- payload data from request ----------
    public function userPayload()
    {
        helper('jwt');
        $authenticationHeader = $this->request->getServer('HTTP_AUTHORIZATION');
        return $decodedToken = getUserPayload($authenticationHeader);
    }

    // Se connecter a la plateforme
    public function login()
    {
        $data = [];
        
        if($this->request->getPost())
        {
            
            $rules = [
                'email' => 'required|min_length[6]|max_length[50]|valid_email',
                'password' => 'required|min_length[6]|max_length[255]|validateUser[email,password]',
            ];

            $errors = [
                'password' => ['validateUser' => "L'adresse mail ou le Mot de passe ne correspond pas!"]
            ];

            $input = $this->getRequestInput($this->request);

            if(!$this->validateRequest($input, $rules, $errors)) {
                return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
            }else{
                
                $email = $this->request->getVar('email');
                $userModel = new UserModel();
                $user = $userModel->get_all($email);
             
                if($user->DESIGNATION_ROLE == "admin") {
                    return $this->getJWTForUser($input['email']);
                }
                elseif($user->DESIGNATION_ROLE == "tenant") {
                    return $this->getJWTForUser($input['email']);
                }
                elseif($user->DESIGNATION_ROLE == "client") {
                    return $this->getJWTForUser($input['email']);
                }
            }
        
        }

        $response = [
            'message' => 'Connectez-vous ou creer un compte'
        ];

        return $this->respond($response, 200);
    }

    //------ Fonction pour creer un jwt pour l'utilisateur
    public function getJWTForUser(string $email, int $responseCode = ResponseInterface::HTTP_OK)
    {
        try{
            $model = new UserModel();
            $user = $model->get_all($email);
            $id = $user->ID_USER;
            $role = $user->DESIGNATION_ROLE;
            // $token = getSignedJWTForUser($id, $email, $role);
            unset($user->MDP_USER);

            helper('jwt');

            return $this->getResponse([
                'access_token' => getSignedJWTForUser($id, $email, $role),
            ]);
        } catch(Exception $exception) {
            return $this->getResponse(
                [
                    'error' => $exception->getMessage(),
                ],
                $responseCode
            );
            // return $this->respond($email);
        }
    }

    // Creation d'un compte
    public function create_count()
    {
        $data = [];
        $roleModel = new RoleModel();

        $rules = [
            'role' => 'required|integer',
            'nom' => 'required|min_length[3]|max_length[50]',
            'postnom' => 'required|max_length[50]',
            'email' => 'required|min_length[3]|max_length[50]|valid_email|is_unique[users.EMAIL_USER]',
            'mdp' => 'required|max_length[50]',
            'mdp_confirm' => 'matches[mdp]',

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
            ];

            $clientModel = new UserModel();
            $clientModel->insert($client);

            $message = ['Client creer avec success'];
            return $this->getResponse($message, ResponseInterface::HTTP_CREATED);

        }
    }
    
    // Detail et mofification du profil
    public function profil($id_user)
    {
        // $current_user = $this->payload()->id;
        $userModel = new UserModel();
        $data = [];

        if($this->request->getPost())
        {
            $rules = [
                'nom' => 'required|min_length[3]|max_length[50]',
                'postnom' => 'required|max_length[50]',
                'email' => 'required|min_length[3]|max_length[50]|valid_email',
                'mdp' => 'required|max_length[50]',
                'mdp_confirm' => 'matches[mdp]',
            ];

            if(!$this->validate($rules))
            {
                $data['validation'] = $this->validator;
            }else{

                $client = [
                    'NOM_USER' => $this->request->getVar('nom'),
                    'POSTNOM_USER' => $this->request->getVar('postnom'),
                    'EMAIL_USER' => $this->request->getVar('email'),
                    'MDP_USER' => $this->request->getVar('mdp'),
                ];

                $clientModel = new UserModel();

                $save = $clientModel->update($id_user,$client);

                if($save) {
                    $data['success'] = "Profil modifié";
                }else{
                    $data['error'] = "Profil non modifié";
                }
            }
        }

        $data['user'] = $userModel->find($id_user);

        return $this->getResponse($data);
    }

    //== Changer la photo de profil
    public function update_picture()
    {
        $rules = [
            'image_user' => "uploaded[image_user]|is_image[image_user]|mime_in[image_user,image/jpg,image/jpeg,image/png]",
        ];
        
        $input = $this->getRequestInput($this->request);

        if(!$this->validateRequest($input, $rules))
        {
            return $this->getResponse($this->validator->getErrors(), ResponseInterface::HTTP_BAD_REQUEST);
        }

        $image = $this->request->getFile('image_user');
        $image->move('uploads/users');

        $client_image = [
            'IMAGE_USER' => 'uploads/users/'.$image->getClientName()
        ];

        $current_user = $this->userPayload()->id;

        $clientModel = new UserModel();
        $save = $clientModel->update($current_user,$client_image);

        $response = ['message' => 'Photo du profil modifié'];
        return $this->getResponse($response, ResponseInterface::HTTP_OK);

    }

    // Suppression du compte d'un client
    public function delete_count()
    {
        $current_user = $this->userPayload()->id;
        $userModel = new UserModel();

        $delete = $userModel->soft_delete($current_user);

        return $this->respondDeleted($delete);
    }

    //== Suppression d'un utilisateur
    public function user_delete($id)
    {
        $clientModel = new UserModel();
        $data = ['ETAT_USER' => 0];
        $active = $clientModel->update($id, $data);

        $response = ['message' => 'Utilisateur supprimé'];

        return $this->getResponse($response, ResponseInterface::HTTP_OK);
    }

    // Se deconnecter
    public function logout()
    {
        // return redirect()->to(site_url('login'));
    }
}
