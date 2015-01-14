<?php

require_once('vendor/autoload.php');

use FreeAgentApi\FreeAgentApi as ApiClient;

$app = new \Slim\Slim(array(
    'debug' => true,
    'mode' => 'development',
    'view' => new \Slim\Views\Twig(),
    'templates.path' => './src/views'
));

$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
);

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$app->apiClient = new ApiClient();

$app->get('/', function () use ($app) {
    $app->render('index.twig.html');
});

$app->get('/contacts/:contactType', function ($contactType) use($app) {
    $contacts = $app->apiClient->getContacts($contactType);
    $app->render('contacts.twig.html',['contacts' => $contacts]);
});

$app->get('/list-expenses', function () use($app) {
    $response = $app->apiClient->getExpenses();
    $app->render('expenses.twig.html',['expenses' => $response]);
});

$app->get('/trial-balance', function () use($app) {
    $response = $app->apiClient->getTrialBalance();
    $app->render('trial-balance.twig.html',['categories' => $response]);
});

$app->get('/invoices', function () use($app) {
    $response = $app->apiClient->getInvoices();
    $app->render('invoices.twig.html',['invoices' => $response]);
});

$app->get('/invoice/create/:contactId', function ($contactId) use($app) {
    $response = $app->apiClient->createInvoice($contactId);
    print_r($response);
});

$app->get('/invoice/update/:invoiceId', function ($invoiceId) use($app) {
    $response = $app->apiClient->updateInvoice($invoiceId);
    print_r($response);
});

$app->get('/invoice/delete/:invoiceId', function ($invoiceId) use($app) {
    $response = $app->apiClient->deleteInvoice($invoiceId);
    print_r($response);
});

$app->run();
