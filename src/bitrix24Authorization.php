<?php
namespace Bitrix24Authorization;
use CurlHelper;

class Bitrix24Authorization
{
    public $bitrix24_access;
    private $app_scope;
    private $app_id;
    private $app_secret;
    private $bitrix24_domain;
    private $bitrix24_login;
    private $bitrix24_password;

    /**
     * Method which returns bitrix24 authorization information in automatic mode.
     * @return array
     * @throws \Exception
     */
    private function authorize()
    {
        $b24_auth_response = CurlHelper::factory('https://' . $this->bitrix24_domain . '/oauth/authorize/')
            ->follow(false)
            ->setGetFields([
                'client_id' => $this->app_id
            ])
            ->exec();
        $b24_domain_cookies = $b24_auth_response['cookies'];
        $b24_location_target = $b24_auth_response['headers']['Location'];

        $b24_auth_response = CurlHelper::factory($b24_location_target)
            ->exec();
        $b24_auth_sessid = $this->getBitrixSessionIdFromCurl($b24_auth_response['content']);
        $b24_auth_cookies = $b24_auth_response['cookies'];

        // Имитируем проверку логина и пароля
        $b24_login_post_data = [
            'SITE_ID' => 's1',
            'sessid' => $b24_auth_sessid,
            'login' => $this->bitrix24_login,
            'password' => $this->bitrix24_password,
            'remember' => '1'
        ];
        $b24_auth_response = CurlHelper::factory('https://auth2.bitrix24.net/bitrix/services/main/ajax.php?action=b24network.authorize.checkLogin')
            ->setPostFields($b24_login_post_data)
            ->setCookies($b24_auth_cookies)
            ->exec();
        $b24_login_check_data = json_decode($b24_auth_response['content'], true);
        if (!is_array($b24_login_check_data) || !array_key_exists('status', $b24_login_check_data) || $b24_login_check_data['status'] != 'success') {
            throw new \Exception('Login and Password check fails at Bitrix24 portal!');
        }

        // Авторизуемся на портале Bitrix24 по проверенным данным...
        $b24_auth_response = CurlHelper::factory('https://auth2.bitrix24.net/bitrix/services/main/ajax.php?action=b24network.authorize.check')
            ->setPostFields($b24_login_post_data)
            ->setCookies($b24_auth_cookies)
            ->exec();
        $b24_auth_cookies = array_merge($b24_auth_cookies, $b24_auth_response['cookies']);

        // Переходим по ссылке авторизации с новыми cookies
        $b24_auth_response = CurlHelper::factory($b24_location_target)
            ->setCookies($b24_auth_cookies)
            ->exec();

        // Dыдергиваем js-redirect из body, который редиректит нас на портал компании
        $b24_js_domain_backurl = $this->getRespondUrlFromCurl($b24_auth_response['content']);

        // Переходим на свой портал компании и получаем ссылку-редирект для авторизации на портале компании
        $b24_auth_response = CurlHelper::factory($b24_js_domain_backurl)
            ->setCookies($b24_domain_cookies)
            ->exec();
        $b24_domain_cookies = array_merge($b24_domain_cookies, $b24_auth_response['cookies']);

        // Собираем ссылку аторизации на своём портале компании
        $b24_domain_backurl_uri = $this->getRespondUrlFromCurl($b24_auth_response['content']);
        $b24_js_domain_backurl = 'https://' . $this->bitrix24_domain . $b24_domain_backurl_uri;

        // Переходим по ссылке своего портала для получения параметра CODE
        $b24_auth_response = CurlHelper::factory($b24_js_domain_backurl)
            ->setCookies($b24_domain_cookies)
            ->follow(false)
            ->exec();

        $b24_auth_code = $this->getBitrixAuthCodeFromCurl($b24_auth_response['headers']['Location']);

        // По полученному CODE формируем запрос для получения ACCESS TOKEN`а
        $get = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->app_id,
            'client_secret' => $this->app_secret,
            'code' => $b24_auth_code,
            'scope' => $this->app_scope
        ];

        $b24_auth_response = CurlHelper::factory('https://' . $this->bitrix24_domain . '/oauth/token/')
            ->setGetFields($get)
            ->setCookies($b24_auth_cookies)
            ->exec();

        $this->bitrix24_access = $b24_auth_response['data'];
        return $this->bitrix24_access;
    }

    /**
     * Method which returns bitrix24 chain URL  from cURL response
     * @param $curl_response
     * @return string
     * @throws \Exception
     */
    private function getRespondUrlFromCurl($curl_response)
    {
        if(!preg_match('~window\.location(\.href)?[\s\=]{1,3}\'(.+?)\'~m', $curl_response, $result)) {
            throw new \Exception("THERE IS NO ~bitrix24 chain URL~ IN CURL ANSWER... <br>\r\nINPUT CURL BODY:  <br>\r\n{$curl_response}");
        }

        $bitrix_respond_url = trim($result[2]);
        return $bitrix_respond_url;
    }

    /**
     * Method which checks, is bitrix24 access token still valid
     * @return bool
     */
    public function is_authorize()
    {
        if(!empty($this->bitrix24_access) && $this->bitrix24_access['expires'] > time())
            return true;

        return false;
    }

    /**
     * Method which checks, is required variables has been defined for further valid script work
     * @return bool
     * @throws \Exception
     */
    private function checkAuthorizationVars ()
    {
        $error = '';
        $error .= empty($this->app_scope) ? "\r\napp_scope with 'setApplicationScope' method <br>" : '';
        $error .= empty($this->app_id) ? "\r\napp_id with 'setApplicationId' method <br>" : '';
        $error .= empty($this->app_secret) ? "\r\napp_secret with 'setApplicationSecret' method <br>" : '';
        $error .= empty($this->bitrix24_domain) ? "\r\nbitrix24_domain with 'setBitrix24Domain' method <br>" : '';
        $error .= empty($this->bitrix24_login) ? "\r\nbitrix24_login with 'setBitrix24Login' method <br>" : '';
        $error .= empty($this->bitrix24_password) ? "\r\nbitrix24_password with 'setBitrix24Password' method <br>" : '';

        if(!empty($error)) throw new \Exception('You need to set this variables to get authorization data: <br>' . $error);
        return true;
    }

    /**
     * Method which returns bitrix24 authorization data or authorized Bitrix24 object if using mesilov/bitrix24-php-sdk
     * @param Bitrix24|null $B24App
     * @return Bitrix24|object
     */
    public function initialize(\Bitrix24\Bitrix24 $B24App = null)
    {
        try {
            $this->checkAuthorizationVars();
        } catch (\Exception $error) {
            die($error->getMessage());
        }


        if(!$this->is_authorize())
            try {
                $this->authorize();
            } catch (\Exception $error) {
                die($error->getMessage());
            }

        if(is_object($B24App)) {
            $B24App->setApplicationScope(array($this->app_scope));
            $B24App->setApplicationId($this->app_id);
            $B24App->setApplicationSecret($this->app_secret);
            $B24App->setDomain($this->bitrix24_domain);
            $B24App->setMemberId($this->bitrix24_access['member_id']);
            $B24App->setAccessToken($this->bitrix24_access['access_token']);
            $B24App->setRefreshToken($this->bitrix24_access['refresh_token']);

            return $B24App;
        }

        return $this->bitrix24_access;
    }

    /**
     * @param $application_scope
     */
    public function setApplicationScope($application_scope)
    {
        $application_scope = str_replace(' ', '', $application_scope);
        $this->app_scope = $application_scope;
    }

    /**
     * @param $application_id
     */
    public function setApplicationId($application_id)
    {
        $this->app_id = $application_id;
    }

    /**
     * @param $application_secret
     */
    public function setApplicationSecret($application_secret)
    {
        $this->app_secret = $application_secret;
    }

    /**
     * @param $bitrix24_domain
     */
    public function setBitrix24Domain($bitrix24_domain)
    {
        $bitrix24_domain = preg_replace('~https?:\/\/([w]{3})?~', '', $bitrix24_domain);

        $this->bitrix24_domain = $bitrix24_domain;
    }

    /**
     * @param $bitrix24_user_login
     */
    public function setBitrix24Login($bitrix24_user_login)
    {
        $this->bitrix24_login = $bitrix24_user_login;
    }

    /**
     * @param $bitrix24_user_password
     */
    public function setBitrix24Password($bitrix24_user_password)
    {
        $this->bitrix24_password = $bitrix24_user_password;
    }

    /**
     * Method which returns private properties if user wants to use them directly
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if(isset($this->{$name}))
            return $this->{$name};
    }

    /**
     * Method which returns bitrix24 SessionId from cURL response
     * @param $curl_response
     * @return string
     * @throws \Exception
     */
    private function getBitrixSessionIdFromCurl($curl_response)
    {
        if(!preg_match('~\'bitrix_sessid\':\'([\d\w]+)\'~', $curl_response, $result)) {
            throw new \Exception("THERE IS NO ~bitrix_sessid~ IN CURL ANSWER... <br>\r\nINPUT CURL BODY:  <br>\r\n{$curl_response}");
        }

        $bitrix_session_id = trim($result[1]);
        return $bitrix_session_id;
    }

    /**
     * Method which returns bitrix24 CODE parameter from cURL response
     * @param $curl_response
     * @return string
     * @throws \Exception
     */
    private function getBitrixAuthCodeFromCurl($curl_response)
    {
        if(!preg_match('~code=([^\&]+)~', $curl_response, $b24_auth_code))
            throw new \Exception("NO PARAMETER ~CODE~ IN BITRIX24 ANSWER: ... <br>\r\nINPUT CURL BODY:  <br>\r\n{$curl_response}");
        $b24_auth_code = $b24_auth_code[1];

        return $b24_auth_code;
    }
}