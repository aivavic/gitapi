<?php

/**
 * Created by PhpStorm.
 * User: viktor_
 * Date: 5/4/15
 * Time: 10:16 PM
 */
error_reporting(E_STRICT | E_ALL);
class GitAuth2 extends CComponent
{

    const OAUTH2_CLIENT_ID = 'cc64d12df7aabfdc420b';
    const OAUTH2_CLIENT_SECRET = '64e88aefd4358240793c5816b7a3adb38da2ca26';

    private $authorizeURL = 'https://github.com/login/oauth/authorize';
    private $tokenURL = 'https://github.com/login/oauth/access_token';
    private $apiURLBase = 'https://api.github.com/';
    private $user_agent;

    protected static $_instance;

    private function __construct(){
$this->start();
    }


    private function __clone(){
    }

    public static function getInstance() {
        // проверяем актуальность экземпляра
        if (null === self::$_instance) {
            // создаем новый экземпляр
            self::$_instance = new self();
        }
        // возвращаем созданный или существующий экземпляр
        return self::$_instance;
    }



    public function start()
    {
        session_start();
        if ($this->get('action') == 'login') {
            $_SESSION['state'] = hash('sha256', microtime(TRUE) . rand() . $_SERVER['REMOTE_ADDR']);
            unset($_SESSION['access_token']);

            $params = array(
                'client_id' => self::OAUTH2_CLIENT_ID,
                'redirect_uri' => 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'],
                'scope' => 'user',
                'state' => $_SESSION['state']
            );

            // Redirect the user to Github's authorization page
            header('Location: ' . $this->authorizeURL . '?' . http_build_query($params));
            die();
        }

// When Github redirects the user back here, there will be a "code" and "state" parameter in the query string
        if ($this->get('code')) {
            // Verify the state matches our stored state
            if (!$this->get('state') || $_SESSION['state'] != $this->get('state')) {
                header('Location: ' . $_SERVER['PHP_SELF']);
                die();
            }

            // Exchange the auth code for a token
            $token = $this->apiRequest($this->tokenURL, array(
                'client_id' => self::OAUTH2_CLIENT_ID,
                'client_secret' => self::OAUTH2_CLIENT_SECRET,
                'redirect_uri' => 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'],
                'state' => $_SESSION['state'],
                'code' => $this->get('code')
            ));
            $_SESSION['access_token'] = $token->access_token;

            header('Location: ' . $_SERVER['PHP_SELF']);
        }

        if ($this->session('access_token')) {
            $query = $this->apiURLBase . 'search/repositories?q=films+language:php&sort=stars&order=desc';
            var_dump($query);
            $user = $this->apiRequest($this->apiURLBase . 'user');
//            $search = $this->apiRequest($query);
            $name = isset($user->name) ? $user->name : 'noname';
            echo '<h3>Logged In</h3>';
            echo '<h4>' . $name . '</h4>';
            echo '<img src = "'. $user->avatar_url . '" style="width: 100px;"/>';
//            $this->repList($user->repos_url);
            echo '<pre>';

//            print_r($user->repos_url);

            echo '</pre>';

        } else {
            echo '<h3>Not logged in</h3>';
            echo '<p><a href="?action=login">Log In</a></p>';
        }

    }

    function repList($url){
        $repositories = $this->apiRequest($url);
        echo '<pre>';

        echo '<ul> Мои репозитории: ';
        foreach($repositories as $repositorie){
            echo '<li><a href="'. $repositorie->html_url . '" >' . $repositorie->full_name . '</a></li>';
        }
        echo '</ul>';
        var_dump($repositories);
        echo '</pre>';
    }
    function apiRequest($url, $post = FALSE, $headers = array())
    {
        $this->user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        if ($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

//        $headers[] = 'Accept: application/json';
        $headers[] = 'Accept: application/vnd.github.moondragon+json';

        if ($this->session('access_token'))
            $headers[] = 'Authorization: Bearer ' . $this->session('access_token');

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
//        var_dump($response);
        return json_decode($response);
    }

    function get($key, $default = NULL)
    {
        return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
    }

    function session($key, $default = NULL)
    {
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }
    function search($query){
        $this->user_agent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)';
        $search = '/search/repositories?q=yii2+language:php&sort=stars&order=desc';
        $url = $this->apiURLBase . $search;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);


//        $headers[] = 'Accept: application/json';
        $headers[] = 'Accept: application/vnd.github.moondragon+json';

        if ($this->session('access_token'))
            $headers[] = 'Authorization: Bearer ' . $this->session('access_token');

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
//        var_dump($response);
        return json_decode($response);
    }

}