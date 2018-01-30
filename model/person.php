<?php namespace Model;
/****        **************************************************************************** PERSON */
class Person extends \App\Model implements \App\Agent {
  const SRC  = '../data/model.xml';
  const PATH = '/model/person/item'; // this represents the place to find and store elements. 
  
  protected function fixture(): array  {
    return [
      '@title'   => '',
      '@id'      => new \App\Slug($this, '@title'),
      '@access'  => '',
      '@created' => new \App\Clock,
      '@updated' => new \App\Clock,
      'log'      => [],  // DSNSLNREQ: how to represent a List/Group.
    ];
  }
  
  public function getName(\DOMElement $context) {
    $parts = explode(' ', $context['@title']);
    return array_combine(['first','last'], [array_shift($parts), array_pop($parts)]);
  }
  
  public function getEmail(\DOMElement $context): string {
    return (string)$context['@access'];
  }
  
  public function sign(\App\Token $token) {
    return [$this['@access'], $this['@id']];
  }  
  
  public function contact(string $subject, string $message) {
    $key = getenv('EMAIL');
    $ch  = curl_init("https://api.postmarkapp.com/email");
    
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER	=> true,
      CURLOPT_HTTPHEADER => [
        'Accept: application/json', 'Content-Type: application/json', "X-Postmark-Server-Token: {$key}"
      ],
      CURLOPT_POSTFIELDS => json_encode([
        'From' => getenv('SERVER_ADMIN'),
        'To'   => $this['email'],
        'Subject'  => $subject,
        'HTMLBody' => $message,
      ]),
    ]);

    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result);
  }
  
}
