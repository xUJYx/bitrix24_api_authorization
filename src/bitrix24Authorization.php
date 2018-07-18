<?php
namespace bitrix24Authorization;


class bitrix24Authorization
{

    private $config;
    public $b24_access;
    public $execute;

    public function __construct($B24App)
    {
        $config = require_once (__DIR__ . '/config.php');
        $this->config = $config['production'];
        $this->b24_access = $this->authorize();
        $this->execute = $this->initialize($B24App);
    }

    private function authorize()
    {
        $_url = 'https://'.$this->config['domain'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $_url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);

        $l = '';
        if(preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }

        curl_setopt($ch, CURLOPT_URL, $l);
        $res = curl_exec($ch);
        preg_match('#name="backurl" value="(.*)"#', $res, $math);

        $post = http_build_query([
            'AUTH_FORM' => 'Y',
            'TYPE' => 'AUTH',
            'backurl' => $math[1],
            'USER_LOGIN' => $this->config['login'],
            'USER_PASSWORD' => $this->config['password'],
            'USER_REMEMBER' => 'Y'
        ]);

        curl_setopt($ch, CURLOPT_URL, 'https://www.bitrix24.net/auth/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $res = curl_exec($ch);

        $l = '';
        if(preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }

        curl_setopt($ch, CURLOPT_URL, $l);
        $res = curl_exec($ch);

        $l = '';
        if(preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }

        curl_setopt($ch, CURLOPT_URL, $l);
        $res = curl_exec($ch);
        curl_setopt($ch, CURLOPT_URL, 'https://'.$this->config['domain'].'/oauth/authorize/?response_type=code&client_id='.$this->config['client_id']);
        $res = curl_exec($ch);

        $l = '';
        if(preg_match('#Location: (.*)#', $res, $r)) {
            $l = trim($r[1]);
        }
        preg_match('/code=(.*)&do/', $l, $code);
        $code = $code[1];

        curl_setopt($ch, CURLOPT_URL, 'https://'.$this->config['domain'].'/oauth/token/?grant_type=authorization_code&client_id='.$this->config['client_id'].'&client_secret='.$this->config['client_secret'].'&code='.$code.'&scope=' .$this->config['scope']);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);

        curl_close($ch);

        return json_decode($res);
    }

    public function is_authorize()
    {
        if($this->b24_access->expires > time())
            return true;

        return false;
    }

    private function initialize($B24App)
    {
        if(!$this->is_authorize())
            $this->authorize();

        $B24App->setApplicationScope(array($this->config['scope']));
        $B24App->setApplicationId($this->config['client_id']);
        $B24App->setApplicationSecret($this->config['client_secret']);

        $B24App->setDomain($this->config['domain']);
        $B24App->setMemberId($this->b24_access->member_id);
        $B24App->setAccessToken($this->b24_access->access_token);
        $B24App->setRefreshToken($this->b24_access->refresh_token);

        return $B24App;
    }
}