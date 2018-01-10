<?php namespace App;

class Token {
  private $algo, $length, $secret;
  public  $expire, $version, $secure = true;
          
  public function __construct(string $config, ?int $expire = null) {
    [$this->algo, $this->length, $this->secret, $this->version] = explode(':', $config);
    $this->secure = $this->version !== 'local';
    $this->expire = ($expire !== null ? $expire : 3600 * 24 * 90) + time();
  }
  
  public function validate(string $hash, string $private, string $public) {
    return hash_equals($hash, $this->encode($private, $public));
  }
  
  public function encode(string $private, string $public) {
    $hash   = hash_hmac($this->algo, $private, $this->secret);
    $public = str_rot13($public);
    $size   = strlen($public);
    $skip   = floor($this->length / $size);
    for($i = $size; $i>=0; $i--) {
      $hash = substr_replace($hash, $public[$i], ($skip * $i), 0);
    }
    return $hash;
  }
  
  public function decode(string $input) {
    $size = strlen($input) - $this->length;
    $skip = floor($this->length / $size);
    $key  = ''; 
    for($i = $size-1; $i>=0; $i--) {
      $key = $input[$i * $skip + $i] . $key;
      $input = substr_replace($input, '', ($skip * $i) + $i, 1);
    }
    return  str_rot13($key);
  }
  
}

class Email {
  
}

/*************        *************************************************************************************/
abstract class Method {
  
  abstract public function session(Token $token, array $set = null);
  
  public $start, $route, $scheme = 'http', $format = 'txt', $params, $data;
  
  static public function Neu(string $method) {
    $ClassName = "\\App\\{$method}";
    return new $ClassName($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true) );
  }

  public function __toString(): string {
    return substr(static::class, 4);
  }
}

class CLI extends Method {
  public $scheme = 'repl';
  public function __construct() {
    $this->start = $timestamp;
    $this->route  = preg_split('/\W/', $_SERVER['argv'][1]);
    $this->params = array_slice($_SERVER['argv'], 2);
  }
  
  public function session(Token $token, array $set = null) {
    return null;
  }
}

class GET extends Method {
  
  public function __construct(float $timestamp) {
    $this->start  = $timestamp;
    $this->route  = array_filter($_GET['_r_']);
    $this->params = array_filter(explode('/', $_GET['_p_']));
    $this->format = $_GET['_e_'] ?: 'html';
    $this->scheme = 'http';
    $this->host   = sprintf('%s://%s', $_SERVER['REQUEST_SCHEME'], $_SERVER['SERVER_NAME']);
  }
  
  public function session(Token $token, array $set = null) {
    if ($set) {
      $hash = $token->encode(...$set);
      setcookie($token->version, $hash, $token->expire, '/', '', $token->secure, true);
    }
    return $_COOKIE[$token->version];
  }
}

class POST extends GET {
  public function __construct(float $timestamp) {
    parent::__construct($timestamp);
    array_unshift($this->params, new Data($_POST));
  }
}


/****         *************************************************************************************/
class Request {

  public $listeners = [], $token;

  public function __construct(Method $method) {
    $this->method = $method;
  }
    
  public function listen (string $scheme, callable $callback): void {
    $this->listeners[$scheme] = $callback;
  }

  public function authenticate(\ReflectionMethod $method): bool {
    $Model = (string)$method->getParameters()[0]->getType();
    $token = new Token(ID);
    if ($hash = $this->method->session($token)) {
      $user  = new $Model( $token->decode($hash) );
      $valid = $token->validate($hash, ...$user());
      $method->setAccessible($valid);
      array_unshift($this->method->params, $user); 
      return $valid;
    }
    return false;
  }
  
  public function response() {
    return $this->listeners[$this->method->scheme]->call($this);
  }
  
  public function delegate(...$route) {
    [$instance, $method] = Controller::Make($this, ...array_replace($route, $this->method->route));
    return $instance->output($method->invokeArgs($instance, $this->method->params));
  }
}


/*
[ ] response should be in control of filtering/reordering DOM presentation
[ ] response should be in charge of caching
[ ] response should be able to return a partial if request is ajax.
[x] I would like to lose the static methods and make the response more fluid
    - look in design patters for way to have request/response talk to one another
*/

/****          *************************************************************************************/
class Response {
  
  private $request, $content;

  public $content_type = [
    'html' => 'Content-Type: application/xhtml+xml; charset=utf-8',
    'json' => 'Content-Type: application/javascript; charset=utf-8',
    'xml'  => 'Content-Type: text/xml; charset=utf-8',
    'svg'  => 'Content-Type: image/svg+xml; charset=utf-8',
    'jpg'  => 'Content-Type: image/jpeg',
    'js'   => 'Content-Type: application/javascript; charset=utf-8',
    'css'  => 'Content-Type: text/css; charset=utf-8'
  ];
  
  public $status = [
    'unauthorized' => 'HTTP/1.0 401 Unauthorized',
    'incorrect'    => '404',
  ];
  
  public function __construct(Request $request) {
    $this->request = $request;
  }
  
  public function redirect($location_url, $code = 302) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Location: {$location_url}", false, $code);
    exit();
  }
  
  public function authorize(Token $token, Model $model) {
    $this->request->method->session($token, $model());
    $this->redirect('/');
  }
  
  public function setHeader() {
    # code...
  }
  
  public function setContent($object): void {
    // object can be a domdocument, or view object
    $this->content = $object;
  }
  
  public function __toString(): string {
    return (string) $this->content;
  }
  
}

function email($to, $subject, $body) {
  $token = getenv('EMAIL');
  $ch = curl_init("https://api.postmarkapp.com/email");
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER	=> true,
    CURLOPT_HTTPHEADER => [
      'Accept: application/json',
      'Content-Type: application/json',
      "X-Postmark-Server-Token: {$token}"
    ],
    CURLOPT_POSTFIELDS => json_encode([
      'From' => $_SERVER['SERVER_ADMIN'],
      'To'   => $to,
      'Subject' => $subject,
      'HTMLBody' => $body,
    ])
  ]);

  $result = curl_exec($ch);
  curl_close($ch);
  return json_decode($result);
}