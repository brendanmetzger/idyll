<?php namespace App;

/****       **************************************************************************************/
class Token {
  private $algo, $size, $secret;
  public  $expire, $version, $secure = true;
          
  public function __construct(array $config, ?int $expire = null) {
    [$this->algo, $this->size, $this->secret, $this->version] = $config;
    $this->secure = $this->version !== 'local';
    $this->expire = ($expire !== null ? $expire : 3600 * 24 * 90) + time();
  }
  
  public function validate(string $hash, string $private, string $public) {
    return hash_equals($hash, $this->encode($private, $public));
  }
  
  public function encode(string $key, string $msg): string {
    return array_reduce(str_split(str_rot13($msg)), \Closure::bind(function($H, $L)  {
      return substr_replace($H, $L, $this->i -= $this->s, 0);
    }, (object)['s'=>floor($this->size/strlen($msg)),'i'=>0]), hash_hmac($this->algo, $key, $this->secret));
  }
  
  public function decode(string $msg): string {
    $size = strlen($msg) - 32;
    $skip = floor($this->size / $size) * -1;
    return str_rot13(array_reduce(range(1, $size), function($o, $i) use($msg, $skip) {
      return $o . $msg[($i * $skip - 1)];
    }, ''));
  }
}

/*************        ****************************************************************************/
abstract class Method {
  public $start, $route, $scheme = 'http', $format = 'txt', $params, $data;
  
  static public function New(string $method) {
    $ClassName = "\\App\\{$method}";
    return new $ClassName($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true) );
  }
  
  public function __toString(): string {
    return substr(static::class, 4);
  }
  
  abstract public function session(Token $token, array $set = null);
}

/****     ****************************************************************************************/
class CLI extends Method {
  public $scheme = 'console';
  public function __construct($timestamp) {
    $this->start = $timestamp;
    $this->route  = array_filter(explode(':', $_SERVER['argv'][1] ?? ''));
    $this->params = array_slice($_SERVER['argv'], 2);
  }
  
  public function session(Token $token, array $set = null) {
    return null;
  }
}

/****     ****************************************************************************************/
class GET extends Method {
  public $direct = false;
  public function __construct(float $timestamp) {    
    $this->start  = $timestamp;
    $this->route  = array_filter($_GET['_r_']);
    $this->params = array_filter(explode('/', $_GET['_p_']));
    $this->format = $_GET['_e_'] ?: 'html';
    $this->host   = sprintf('%s://%s', getenv('REQUEST_SCHEME'), getenv('SERVER_NAME'));
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
  
  public function __construct(float $timestamp) {
    parent::__construct($timestamp);
    array_unshift($this->params, new Data($_POST));
  }
}

/****         ************************************************************************************/
class Request {

  static public $mime = [
    'html' => 'application/xhtml+xml',
    'json' => 'application/javascript',
    'js'   => 'application/javascript',
    'svg'  => 'image/svg+xml',
    'jpg'  => 'image/jpeg',
    'xml'  => 'text/xml',
    'css'  => 'text/css'
  ];
  
  public $token;

  public function __construct(Method $method) {
    $this->method = $method;
    $this->token  = new Token(ID);
  }
    
  // TODO this seems like a chunk of nonsense... simplify!
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
  
  public function authorize(string $type, string $message, string $key) {
    $id = $this->token->decode($message);
    if ($this->token->validate($message, $key, $id)) {
      $this->method->session($this->token, Factory::Model($type)->newInstance($id)->sign($this->token));
    }
  }
    
  public function delegate(Response $response, ...$defaults): Controller {
    $this->method->route = array_replace($defaults, $this->method->route);
    [$class, $action] = $this->method->route;
    
    $instance = Factory::Controller($class)->newInstance($this, $response);
    $method   = new \ReflectionMethod($instance, $this->method . $action);
    
    if ($method->isProtected() && ! $this->authenticate($method)) {
      $method = new \ReflectionMethod($instance, $this->method . 'login');
    }

    return $instance->output($method->invokeArgs($instance, $this->method->params));
  }
}

/****          ***********************************************************************************/
class Response {
  
  private $request, $template, $content, $output, $handler = [], $header = [];
  
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
  
  public function setTemplate(View $view): void {
    $this->template = $view;
  }
  
  public function setContent($object): void {
    $this->content = $object;
  }
  
  public function compose($data = []) {
    return $this->output = ($this->template ? $this->template->set('content', $this->content)->render($data) : $this->content);
  }
  
  public function __toString()
  {
    foreach ($this->header as $header) header(...$header);
    return (string) $this->output;
  }
}