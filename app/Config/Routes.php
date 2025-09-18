<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Frontend routes - Publicly accessible pages
$routes->get('/', 'Frontend\HomeController::index', ['as' => 'homeIndex']);
$routes->get('blog', 'Frontend\BlogController::index', ['as' => 'blogIndex']);
$routes->get('article/(:segment)', 'Frontend\ArticleController::article/$1', ['as' => 'articleIndex']);

// Authentication routes - Login, logout, register
$routes->get('login', 'Backend\AuthController::login', ['as' => 'loginIndex']);
$routes->post('login', 'Backend\AuthController::authenticate', ['as' => 'authIndex']);
$routes->get('logout', 'Backend\AuthController::logout', ['as' => 'logoutIndex']);
$routes->get('register', 'Backend\AuthController::register', ['as' => 'registerIndex']);
$routes->post('register', 'Backend\AuthController::createUser', ['as' => 'postUserIndex']);

// Protected routes - Require authentication via 'auth' filter
$routes->group('', ['filter' => 'auth'], function ($routes) {
    // Article management routes
    $routes->post('make-article', 'Backend\EditArticleController::create', ['as' => 'postIndex']);
    $routes->get('create-article', 'Backend\EditArticleController::showForm', ['as' => 'createIndex']);
    $routes->get('edit-article/(:num)', 'Backend\EditArticleController::showForm/$1', ['as' => 'editIndex']);
    $routes->post('update-article/(:num)', 'Backend\EditArticleController::update/$1', ['as' => 'updateIndex']);
    $routes->post('delete-article/(:num)', 'Backend\EditArticleController::delete/$1', ['as' => 'deleteIndex']);
    $routes->post('update-state/(:num)', 'Backend\EditArticleController::updateState/$1', ['as' => 'updateStateIndex']);
    
    // User management routes
    $routes->get('userPage', 'Backend\AuthController::userPage', ['as' => 'userPageIndex']);
    $routes->post('updateUser/(:num)', 'Backend\AuthController::updateUser/$1', ['as' => 'updateUserIndex']);
    $routes->post('deleteUser/(:num)', 'Backend\AuthController::deleteUser/$1', ['as' => 'deleteUserIndex']);
});
