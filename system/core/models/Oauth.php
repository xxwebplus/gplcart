<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache,
    gplcart\core\Handler;
use gplcart\core\helpers\Url as UrlHelper,
    gplcart\core\helpers\Curl as CurlHelper,
    gplcart\core\helpers\Session as SessionHelper;

/**
 * Manages basic behaviors and data related to Oauth 2.0 functionality
 */
class Oauth extends Model
{

    /**
     * Curl helper instance
     * @var \gplcart\core\helpers\Curl $curl
     */
    protected $curl;

    /**
     * Url helper instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Session helper instance
     * @var \gplcart\core\helpers\Session $session
     */
    protected $session;

    /**
     * @param CurlHelper $curl
     * @param SessionHelper $session
     * @param UrlHelper $url
     */
    public function __construct(CurlHelper $curl, SessionHelper $session,
            UrlHelper $url)
    {
        parent::__construct();

        $this->url = $url;
        $this->curl = $curl;
        $this->session = $session;
    }

    /**
     * Returns an Oauth provider
     * @param string $id
     * @return array
     */
    public function getProvider($id)
    {
        $providers = $this->getProviders();

        if (empty($providers[$id])) {
            return array();
        }

        return $providers[$id];
    }

    /**
     * Returns an array of Oauth providers
     * @param array $data
     * @return string
     */
    public function getProviders(array $data = array())
    {
        $providers = &Cache::memory(array(__METHOD__ => $data));

        if (isset($providers)) {
            return $providers;
        }

        $providers = array();
        $this->hook->fire('oauth.providers', $providers);

        foreach ($providers as $provider_id => &$provider) {
            $provider += array('type' => '', 'id' => $provider_id, 'status' => true);
            if (isset($data['type']) && $data['type'] !== $provider['type']) {
                unset($providers[$provider_id]);
                continue;
            }
            if (isset($data['status']) && $data['status'] != $provider['status']) {
                unset($providers[$provider_id]);
            }
        }

        return $providers;
    }

    /**
     * Returns an array of authorization URL query
     * @param array $provider
     * @param array $params
     * @return array
     */
    public function getQueryAuth(array $provider, array $params = array())
    {
        $params += array(
            'response_type' => 'code',
            'scope' => $provider['scope'],
            'state' => $this->buildState($provider)
        );

        $params += $this->getDefaultQuery($provider);

        if (isset($provider['handlers']['auth'])) {
            // Call per-provider query handler
            $params = $this->call('auth', $provider, $params);
        }

        return $params;
    }

    /**
     * Returns default query data for the user authorization process
     * @param array $provider
     * @return array
     */
    protected function getDefaultQuery(array $provider)
    {
        return array(
            'client_id' => $provider['settings']['client_id'],
            'redirect_uri' => $this->url->get('oauth', array(), true)
        );
    }

    /**
     * Returns a query for the authorization request
     * @param array $provider
     * @param array $params
     * @return array
     */
    public function getQueryToken(array $provider, array $params = array())
    {
        $default = array(
            'grant_type' => 'authorization_code',
            'client_secret' => $provider['settings']['client_secret']
        );

        $default += $this->getDefaultQuery($provider);
        return array_merge($default, $params);
    }

    /**
     * Returns an authorization URL for the given provider
     * @param array $provider
     * @param array $params
     * @return string
     */
    public function url(array $provider, array $params = array())
    {
        $query = $this->getQueryAuth($provider, $params);
        return $this->url->get($provider['url']['auth'], $query, true);
    }

    /**
     * Build state code
     * @param array $provider
     * @return string
     */
    protected function buildState(array $provider)
    {
        $data = array(
            'id' => $provider['id'],
            'url' => $this->url->get('', array(), true), // Current absolute URL
            'key' => gplcart_string_random(4), // Make resulting hash unique
        );

        // Base 64 Url safe encoding
        $state = gplcart_string_encode(json_encode($data));

        // Memorize in session
        $this->setState($state, $provider['id']);
        return $state;
    }

    /**
     * Returns an array of data from encoded state code
     * @param string $string
     * @return array
     */
    public function parseState($string)
    {
        return json_decode(gplcart_string_decode($string), true);
    }

    /**
     * Save a state code in the session
     * @param string $state
     * @param string $provider_id
     */
    public function setState($state, $provider_id)
    {
        $this->session->set("oauth.state.$provider_id", $state);
    }

    /**
     * Returns a saved state data from the session
     * @param string $provider_id
     * @return string
     */
    public function getState($provider_id)
    {
        return $this->session->get("oauth.state.$provider_id");
    }

    /**
     * Save a token data in the session
     * @param string $token
     * @param string $provider_id
     */
    public function setToken($token, $provider_id)
    {
        if (isset($token['expires_in'])) {
            $token['expires'] = GC_TIME + $token['expires_in'];
        }

        $this->session->set("oauth.token.$provider_id", $token);
    }

    /**
     * Whether a token for the given provider is valid
     * @param string $provider_id
     * @return bool
     */
    public function isValidToken($provider_id)
    {
        $token = $this->getToken($provider_id);

        return isset($token['access_token'])//
                && isset($token['expires'])//
                && GC_TIME < $token['expires'];
    }

    /**
     * Returns a saved token data from the session
     * @param string $provider_id
     * @return string
     */
    public function getToken($provider_id)
    {
        return $this->session->get("oauth.token.$provider_id");
    }

    /**
     * Whether the state is actual
     * @param string $state
     * @param string $provider_id
     * @return bool
     */
    public function isValidState($state, $provider_id)
    {
        return gplcart_string_equals($state, $this->getState($provider_id));
    }

    /**
     * Performs request to get access token
     * @param array $provider
     * @param array $query
     * @return array
     */
    public function requestToken(array $provider, array $query)
    {
        $this->hook->fire('oauth.request.token.before', $provider, $query);

        $response = $this->curl->post($provider['url']['token'], array('fields' => $query));
        $token = json_decode($response, true);

        $this->hook->fire('oauth.request.token.after', $provider, $query, $token);
        return $token;
    }

    /**
     * Returns an array of requested token data
     * @param array $provider
     * @param array $params
     * @return array
     */
    public function exchangeToken(array $provider, array $params = array())
    {
        if ($this->isValidToken($provider['id'])) {
            return $this->getToken($provider['id']);
        }

        if (isset($provider['handlers']['token'])) {
            $token = $this->call('token', $provider, $params);
        } else {
            $token = $this->requestToken($provider, $params);
        }

        $this->setToken($token, $provider['id']);
        return $token;
    }

    /**
     * Generate and sign a JWT token
     * @param array $data
     * @return string
     * @throws \InvalidArgumentException
     * @link https://developers.google.com/accounts/docs/OAuth2ServiceAccount
     */
    public function generateJwt(array $data)
    {
        $data += array('lifetime' => 3600);

        if (strpos($data['certificate_file'], GC_FILE_DIR) !== 0) {
            $data['certificate_file'] = GC_FILE_DIR . '/' . $data['certificate_file'];
        }

        if (!is_readable($data['certificate_file'])) {
            throw new \InvalidArgumentException('Private key does not exist');
        }

        $key = file_get_contents($data['certificate_file']);
        $header = array('alg' => 'RS256', 'typ' => 'JWT');

        $params = array(
            'iat' => GC_TIME,
            'scope' => $data['scope'],
            'aud' => $data['token_url'],
            'iss' => $data['service_account_id'],
            'exp' => GC_TIME + $data['lifetime']
        );

        $encodings = array(
            base64_encode(json_encode($header)),
            base64_encode(json_encode($params)),
        );

        $certs = array();
        if (!openssl_pkcs12_read($key, $certs, $data['certificate_secret'])) {
            throw new \InvalidArgumentException('Could not parse .p12 file');
        }

        if (!isset($certs['pkey'])) {
            throw new \InvalidArgumentException('Could not find private key in .p12 file');
        }

        $sig = '';
        $input = implode('.', $encodings);
        if (!openssl_sign($input, $sig, openssl_pkey_get_private($certs['pkey']), OPENSSL_ALGO_SHA256)) {
            throw new \InvalidArgumentException('Could not sign data');
        }

        $encodings[] = base64_encode($sig);
        return implode('.', $encodings);
    }

    /**
     * Does main authorization process
     * @param array $provider
     * @param array $params
     * @return bool
     */
    public function process(array $provider, $params)
    {
        $this->hook->fire('oauth.process.before', $provider, $params);
        $result = $this->call('process', $provider, $params);
        $this->hook->fire('oauth.process.after', $provider, $params, $result);
        return $result;
    }

    /**
     * Call a provider handler
     * @param string $handler
     * @param array $provider
     * @param array $params
     * @return mixed
     */
    protected function call($handler, array $provider, $params)
    {
        $providers = $this->getProviders();
        return Handler::call($providers, $provider['id'], $handler, array($params, $provider, $this));
    }

    /**
     * Returns an array of requested token for "server-to-server" authorization
     * @param array $provider
     * @param array $jwt
     * @return boolean
     * @link https://developers.google.com/accounts/docs/OAuth2ServiceAccount
     */
    public function exchangeTokenServer($provider, array $jwt)
    {
        if ($this->isValidToken($provider['id'])) {
            return $this->getToken($provider['id']);
        }

        $jwt += array(
            'scope' => $provider['scope'],
            'token_url' => $provider['url']['token']
        );

        $request = array(
            'assertion' => $this->generateJwt($jwt),
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer'
        );

        $token = $this->requestToken($provider, $request);
        $this->setToken($token, $provider['id']);
        return $token;
    }

}