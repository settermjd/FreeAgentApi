<?php

namespace FreeAgentApi;

use OAuth2\Client as OAuth2Client;

class FreeAgentApi
{
    const BASEURL = 'https://api.freeagent.com/v2';

    const IDENTIFIER = '';

    const SECRET = '';

    /**
     * @var Oauth2client
     */
    protected $_client;

    /**
     * @var string
     */
    protected $_scriptUrl;

    public function __construct()
    {
        $this->_client = new OAuth2Client(self::IDENTIFIER, self::SECRET);
        $this->_scriptUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
        $this->_initClient();
        setlocale(LC_MONETARY, 'de_DE');
    }

    protected function _initClient()
    {
        if (empty($_GET['code']) && empty($_GET['token'])) {
            $auth_url = $this->_client->getAuthenticationUrl(
                self::BASEURL . '/approve_app',
                $this->_scriptUrl
            );
            header('Location: ' . $auth_url);
            exit;
        } elseif (isset($_GET['code'])) {
            $response = $this->_client->getAccessToken(
                self::BASEURL . '/token_endpoint',
                'authorization_code',
                array(
                    'code' => $_GET['code'],
                    'redirect_uri' => $this->_scriptUrl
                )
            );

            $token = $response['result']['access_token'];
            header(
                'Location: ' . $this->_scriptUrl . '?token=' . $token
            );
        } elseif (isset($_GET['token'])) {
            $this->_client->setAccessToken($_GET['token']);
            $this->_client->setAccessTokenType(
                OAuth2Client::ACCESS_TOKEN_BEARER
            );
        }
    }

    /**
     * Utility function to simplify making requests on the API
     *
     * @param string $apiPath
     * @param string $requestMethod
     * @param array $apiParams
     * @return array
     * @throws \OAuth2\Exception
     */
    protected function makeRequest($apiPath, $apiParams = array(), $requestMethod = "GET")
    {
        return $this->_client->fetch(
            self::BASEURL . $apiPath,
            $apiParams,
            $requestMethod,
            array('User-Agent' => 'Example app')
        );
    }

    /**
     * Retrieves a list of current expenses
     *
     * @param string $contactType The type of contact to retrieve/search for
     * @param string $sort        How to sort the search results
     */
    public function getContacts($contactType="all", $sort="name")
    {
        $response = $this->makeRequest(
            '/contacts',
            array(
                'view' => $contactType,
                'sort' => $sort
            ),
            "GET"
        );

        return $response['result']['contacts'];
    }

    /**
     * Retrieves a list of current expenses
     */
    public function getExpenses()
    {
        $response = $this->makeRequest(
            '/expenses',
            array(
                'from_date' => '2012-01-01',
                'to_date' => '2015-01-01'
            ),
            "GET"
        );
        return $response['result']['expenses'];
    }

    /**
     * Retrieves a list of current expenses
     *
     * @param string $date
     */
    public function getTrialBalance(\DateTime $date=null)
    {
        if (is_null($date) || !($date instanceof \DateTime)) {
            $date = new \DateTime();
        }

        $response = $this->makeRequest(
            '/accounting/trial_balance/summary',
            array(
                'date' => $date->format('Y-m-d')
            ),
            "GET"
        );
        return $response['result']['trial_balance_summary'];
    }

    /**
     * Retrieves a list of current invoices
     *
     * @param string $invoiceType The type of invoice to retrieve
     */
    public function getInvoices($invoiceType="all", $sort="updated_at")
    {
        $response = $this->makeRequest(
            '/invoices',
            array(
                'view' => $invoiceType,
                'sort' => $sort
            ),
            "GET"
        );
        return $response['result']['invoices'];
    }

    /**
     * Retrieves a list of current expenses
     */
    public function createInvoice($contactId)
    {
        $requestData = array(
            'invoice' => array(
                'contact' => 'https://api.freeagent.com/v2/contacts/' . $contactId,
                'status' => 'Draft',
                'dated_on' => '2015-01-13',
                'currency' => 'GBP',
                'payment_terms_in_days' => 30,
                'ec_status' => 'non-ec',
                'exchange_rate' => '1.1',
                'reference' => '001',
                'comments' => 'added by API',
                'invoice_items' => array(
                    array(
                        'item_type' => 'products',
                        'quantity' => '1.0',
                        'price' => '100.0',
                        'description' => 'a simple product',
                    )
                )
            )
        );

        $response = $this->makeRequest(
            '/invoices',
            $requestData,
            "POST",
            array(
                "Content-Type: application/json"
            )
        );

        return $response;
    }

    /**
     * Retrieves a list of current expenses
     */
    public function updateInvoice($invoiceId, $contactId)
    {
        $requestData = array(
            'contact' => 'https://api.freeagent.com/v2/contacts/' . $contactId,
            'dated_on' => '2015-01-13T00:00:00+00:00',
            'currency' => 'GBP',
            'payment_terms_in_days' => 30,
            'ec_status' => 'non-ec',
            'exchange_rate' => '1.1',
            'reference' => '001',
            'status' => 'Draft',
            'invoice_items' => array(
                array(
                    'item_type' => 'products',
                    'quantity' => '1.0',
                    'price' => '100.0',
                    'description' => 'a simple product',
                ),
                array(
                    'item_type' => 'hours',
                    'quantity' => '3.0',
                    'price' => '200.0',
                    'description' => 'a simple set of billable hours',
                )
            )
        );

        $response = $this->makeRequest(
            '/invoices/' . $invoiceId,
            $requestData,
            "PUT"
        );

        return $response;
    }

    /**
     * Retrieves a list of current expenses
     */
    public function deleteInvoice($invoiceId)
    {
        return $this->makeRequest(
            '/estimates/' . $invoiceId,
            "DELETE"
        );
    }
}
