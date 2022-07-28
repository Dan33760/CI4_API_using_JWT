<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

//-------------------Login route
// $routes->match(['get', 'post'], 'login', 'UserController::login', ["filter" => "noauth"]);
$routes->add('login', 'UserController::login');
$routes->post('create_count', 'UserController::create_count');
$routes->get("delete_count", "UserController::delete_count");
$routes->get('logout', 'UserController::logout');
$routes->match(['get', 'post'], "profil/(:num)", "UserController::profil/$1");
$routes->get("user_active/(:num)", "AdminController::user_active/$1");
$routes->get("produit", "ProduitController::index");
$routes->get("user_delete/(:num)", "UserController::user_delete/$1");




// -- routes pour le controller boutique
$routes->group('boutique', function($routes) {
    $routes->get("active_store/(:num)", "BoutiqueController::active_store/$1");
    $routes->get("boutique_view/(:num)", "BoutiqueController::boutique_view/$1");
    $routes->get("boutique_delete/(:num)", "BoutiqueController::deleteBoutique/$1");
    $routes->get("update_picture/(:num)", "UserController::update_picture/$1");///////////////////////////////////////////
});

//-------------------Admin Routes
$routes->group("admin", function ($routes) {
    
    // -- routes pour le controller admin
    $routes->get("/", "AdminController::index");
    $routes->get("users", "AdminController::users");
    $routes->get("get_roles", "AdminController::get_roles");
    $routes->post("user_add", "AdminController::user_add");
    $routes->get("boutiques/(:num)", "AdminController::boutiques/$1");
    
    // -- routes pour le controller User
});

//--------------------Tenant routes
$routes->group("tenant", function ($routes) {
    $routes->get("/", "TenantController::index");

    // -- routes pour le controller boutique
    $routes->match(['get', 'post'], "boutique", "BoutiqueController::index");
    $routes->post("boutique_edit/(:num)", "BoutiqueController::boutique_edit/$1");
    
    // -- routes pour le controller produit
    $routes->post("produit_add/(:num)", "ProduitController::produit_add/$1");
    $routes->post("produit_edit/(:num)/(:num)", "ProduitController::produit_edit/$1/$2");
    $routes->get("produit_active/(:num)", "ProduitController::produit_active/$1");
    $routes->get("produit_delete/(:num)", "ProduitController::produit_delete/$1");
    
    // -- routes pour le controller client
    $routes->match(['get', 'post'], "client", "ClientController::index");/////////////////////////////////////////
    $routes->post("client_add/(:num)", "ClientController::client_add/$1");
    $routes->get("client_active/(:num)", "ClientController::client_active/$1");
});
//--------------------Client routes
$routes->group("client", function ($routes) {
    $routes->get("get_boutiques", "ClientController::index");
    $routes->get("add_boutique/(:num)", "ClientController::add_boutique/$1");
    $routes->get("view_produit/(:num)", "ClientController::view_produit/$1");
    $routes->match(['get', 'post'], "user_boutique", "ClientController::boutique");
    
    // -- routes pour le controller panier // =========/////////////////////==========////////////////////=========/////////////
    $routes->post("add_panier/(:num)", "PanierController::add_panier/$1");
    $routes->match(['get', 'post'], "panier_client/(:num)", "PanierController::panier_client/$1");
    $routes->match(['get', 'post'], "panier_detail/(:num)/(:num)", "PanierController::panier_detail/$1/$2");
    $routes->get("panier_delete_produit/(:num)/(:num)/(:num)", "PanierController::panier_delete_produit/$1/$2/$3");
    $routes->get("valider_panier/(:num)/(:num)", "PanierController::valider_panier/$1/$2");
    $routes->get("panier", "PanierController::panier");

});
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
