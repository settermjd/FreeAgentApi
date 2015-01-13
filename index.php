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
    $app->render('index.twig.html',['hello' => 'hello world']);
});

$app->get('/contacts/:contactType', function ($contactType) use($app) {
    echo "<h1>Expenses</h1>";
    $contacts = $app->apiClient->getContacts($contactType);
    $app->render('contacts.twig.html',['contacts' => $contacts]);
});

$app->get('/list-expenses', function () use($app) {
    echo "<h1>Expenses</h1>";
    $expenses = $app->apiClient->getExpenses();
    if (count($expenses)) {
        foreach ($expenses as $expense) {
            printf ("Currency: %s, Gross Value: %s<br />",
                $expense['currency'],
                $expense['gross_value']
            );
        }
    }
});

$app->get('/trial-balance', function () use($app) {
    $response = $app->apiClient->getTrialBalance();
    $app->render('trial-balance.twig.html',['hello' => 'hello world']);
});

$app->get('/invoices', function () use($app) {
    $response = $app->apiClient->getInvoices();
    $app->render('invoices.twig.html',['hello' => 'hello world']);
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