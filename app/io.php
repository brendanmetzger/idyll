<?php namespace App;

/*************        *************************************************************************************/
abstract class Method {
  
  abstract public function session(?string $token = null, int $expire = 7500000): array;
  
  public $start, $route, $scheme = 'http', $format = 'txt', $params, $data;
  
  static public function FACTORY(string $method) {
    $class = "\\App\\{$method}";
    return new $class( $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true) );
  }

  public function __toString(): string {
    return substr(static::class, 4);
  }
  
  public function token($key, ?string $compare = null) {
    $token = hash_hmac('sha256', $key, getEnv('SECRET'));
    return $compare ? $token === $compare : $token;
  }
  
}

class CLI extends Method {
  public $scheme = 'repl';
  public function __construct() {
    $this->start = $timestamp;
    $this->route  = preg_split('/\W/', $_SERVER['argv'][1]);
    $this->params = array_slice($_SERVER['argv'], 2);
  }
  
  public function session(?string $token = null, int $expire = 7500000): array {
    return ['','',''];
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
  
  public function session(?string $token = null, int $expire = 7500000): array {
    // to destroy, set expires to negative number;
    if ($token !== null) {
      setcookie('token', $token, $expire + time(), '/', '', getenv('MODE') !== 'local', true);
    }
    return explode('.', $_COOKIE['token'] ?? '..');
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

  public $listeners = [];

  public function __construct(Method $method) {
    $this->method = $method;
  }
  
  public function listen (string $scheme, callable $callback): void {
    $this->listeners[$scheme] = $callback;
  }

  public function authenticate(\ReflectionMethod $method): bool {
    $model = (string)$method->getParameters()[0]->getType();
    [$id, $token] = $this->method->session();
    if ($id && $token) {
      $instance = new $model($id);
      $status = $this->method->token($instance, $token);
      $method->setAccessible($status);
      array_unshift($this->method->params, $instance); 
      return $status;
    }
    return false;
  }
  
  public function response() {
    return $this->listeners[$this->method->scheme]->call($this);
  }
  
  public function delegate(...$route) {
    [$instance, $method] = Controller::FACTORY($this, ...array_replace($route, $this->method->route));
    return $instance->output($method->invokeArgs($instance, $this->method->params));
  }
}


/*
[ ] response should be in control of filtering/reordering DOM
[ ] response should be in charge of caching
[ ] response should be able to return a partial if request is ajax.
[x] I would like to lose the static methods and make the response more fluid
    - look in design patters for way to have request/response talk to one another
*/

/****          *************************************************************************************/
class Response {
  
  private $request;
  
  public $content = [
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
  
  public function authorize(Model $model) {
    $this->request->method->session(sprintf('%s.%s', $model['@id'], $this->request->method->token($model)));
    $this->redirect('/');
  }
  
}

function email($to, $subject, $body) {
  $token = getenv('EMAIL_TOKEN');
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