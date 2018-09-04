<?php namespace App;

/****       ****************************************************************************** TOKEN */
class Token {
  private $algo, $size, $key, $valid = false;
  public  $expire, $version, $secure = true;
          
  public function __construct(array $config, ?int $expire = null) {
    [$this->algo, $this->key, $this->version] = $config;
    $this->secure = $this->version !== 'local';
    $this->expire = ($expire !== null ? $expire : 3600 * 24 * 90) + time();
  }
  
  public function validate(string $hash, string $private, string $public) {
    return $this->valid = hash_equals($hash, $this->encode($private, $public));
  }
  
  public function encode(string $salt, string $msg): string {
    return array_reduce(str_split(str_rot13($msg)), \Closure::bind(function($H, $L)  {
      return substr_replace($H, $L, $this->i -= $this->s, 0);
    }, (object)[
      's' => floor(strlen(hash($this->algo, '')) / strlen($msg)),'i' => 0
    ]), hash_hmac($this->algo, $salt, $this->key));
  }
  
  public function decode(string $msg): string {
    $width = strlen(hash($this->algo, ''));
    $size = strlen($msg) - $width;
    $skip = floor($width / $size) * -1;
    return str_rot13(array_reduce(range(1, $size), function($o, $i) use($msg, $skip) {
      return $o . $msg[($i * $skip - 1)];
    }, ''));
  }
}

/*************        ******************************************************************* METHOD */
abstract class Method {
  public $route, $format, $params, $data, $scheme = 'http';
  
  public static $ctypes = [
    'html' => 'Content-Type: application/xhtml+xml; charset=utf-8',
    'json' => 'Content-Type: application/javascript; charset=utf-8',
    'xml'  => 'Content-Type: text/xml; charset=utf-8',
    'svg'  => 'Content-Type: image/svg+xml; charset=utf-8',
    'jpg'  => 'Content-Type: image/jpeg',
    'png'  => 'Content-Type: image/png',
    'js'   => 'Content-Type: application/javascript; charset=utf-8',
    'css'  => 'Content-Type: text/css; charset=utf-8',
    'txt'  => 'Content-Type: text/plain; charset=utf-8',
  ];
  
  public function setDefaultRoute(...$route) {
    $this->route = array_replace($route, $this->route);
  }
    
  public function __toString(): string {
    return substr(static::class, 4);
  }
  
  abstract public function session(Token $token, array $set = null);
}

class OPTIONS {
  public function __construct() {
    exit();
  }
}

/****     ****************************************************************************************/
class CLI extends Method {
  public $scheme = 'console', $format = 'txt';
  public function __construct() {
    $this->params = array_slice($_SERVER['argv'], 1);
    $this->route  = array_shift($this->params);
  }
  
  public function session(Token $token, array $set = null) {
    return null;
  }
}

/****     ****************************************************************************************/
class GET extends Method {
  public $direct = false;
  public function __construct() {
    $this->format = isset(Method::$ctypes[$_GET['ext'] ?? '']) ? $_GET['ext']: 'html';
    $this->params  = array_filter(explode('/', $_GET['route'] ?? ''));
    $this->route   = array_splice($this->params, 0, 2);
    $this->host   = sprintf('https://%s', getenv('SERVER_NAME'));
    $this->direct = stripos(getenv('HTTP_REFERER'), $this->host) === 0;
    $this->path   = getenv('REQUEST_URI');
  }
  
  public function session(Token $Tok, array $set = null) {
    if ($set) {
      setcookie($Tok->version, $Tok->encode(...$set), $Tok->expire, '/', '', $Tok->secure, true);
    }
    return $_COOKIE[$Tok->version];
  }
}

/****      ***************************************************************************************/
class POST extends GET {
  
  public function __construct() {
    parent::__construct();
    $_POST['_BODY'] = file_get_contents('php://input');
    array_unshift($this->params, new Data($_POST));
  }
}

/****         ************************************************************************** REQUEST */
class Request {
  
  public $token;

  public function __construct(Method $method) {
    $this->method = $method;
    $this->token  = new Token(ID);
    $this->content_type = Method::$ctypes[$method->format];
  }
    
  // TODO simplify!
  private function authenticate(\ReflectionMethod $method): bool {
    if ($hash = $this->method->session($this->token)) {
      $Model = (string) $method->getParameters()[0]->getType();
      $user  = new $Model( $this->token->decode($hash) );
      $valid = $this->token->validate($hash, ...$user->sign($this->token));
      $method->setAccessible($user instanceof Agent);
      array_unshift($this->method->params, $user); 
      return $valid;
    }
    return false;
  }
  
  public function authorize(string $type, string $message, string $key): bool {
    $id = $this->token->decode($message);
    if ($this->token->validate($message, $key, $id)) {
      return $this->method->session($this->token, Factory::Model($type)->newInstance($id)->sign($this->token));
    }
    return false;
  }
    
  public function delegate(Response $response): Controller {
    [$class, $action] = $this->method->route;
    
    $instance = Factory::Controller($class)->newInstance($this, $response);
    $method   = new \ReflectionMethod($instance, $this->method . $action);
    
    if ($method->isProtected() && ! $this->authenticate($method)) {
      $method = new \ReflectionMethod($instance, $this->method . 'login');
    }

    return $instance->output($method->invokeArgs($instance, $this->method->params));
  }
}

/* TODO
[ ] Consider implementing a way users and guests can see same page, with users having rendered session vars
[ ] response should be in control of filtering/reordering DOM presentation
[ ] response should be in charge of caching
[ ] response should be able to return a partial if request is ajax.
*/
/****          ************************************************************************ RESPONSE */
class Response {
  
  private $request, $view, $content, $output, $handler = [], $header = [];
  
  public function __construct(Request $request) {
    $this->request = $request;
  }
  
  public function handle (string $scheme, callable $callback): void {
    $this->handler[$scheme] = $callback;
  }
  
  public function prepare(?string $type = null): Controller {
    return $this->handler[$type ?: $this->request->method->scheme]->call($this, $this->request);
  }
  
  public function redirect(string $url, $code = 302) {
    $this->setHeader("Cache-Control: no-text, no-cache, must-revalidate, max-age=0");
    $this->setHeader("Cache-Control: post-check=0, pre-check=0", false);
    $this->setHeader("Pragma: no-cache");
    $this->setHeader("Location: {$url}", false, $code);
  }
  
  public function setHeader(string $header, $replace = true, $code = null) {
    $this->header[] = [$header, $replace, $code];
  }
  
  public function setView(View $view): void {
    $this->view = $view;
  }
  
  public function setContent($object): void {
    $this->content = $object;
  }
  
  # TODO should not hardcode the 'content' keywor
  public function compose($data = []) {
    return $this->output = ($this->view ? $this->view->set('content', $this->content)->render($data) : $this->content);
  }
  
  public function __toString() {
    foreach ($this->header as $header) header(...$header);
    return (string) $this->output;
  }
}