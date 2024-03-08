# BeautyAList SDK OpenID for PHP #
<dl>
  <dt>License</dt><dd>Apache 2.0</dd>
</dl>
BeautyAList SDK OpenID provides access to the BeautyAList API for the applications written in the PHP language.

## Requirements
PHP 5.6.0 and later.

## Installation
You can download the [latest release](https://github.com/minininc/BeautyAList-SDK-OpenID/releases). Then include the `init.php` file.
```php
require_once '/path/to/BeautyAList-SDK-OpenID/init.php';
```

## Dependencies
The bindings require the following extensions in order to work properly:
-   [`curl`](https://secure.php.net/manual/en/book.curl.php)
-   [`json`](https://secure.php.net/manual/en/book.json.php)
-   [`mbstring`](https://secure.php.net/manual/en/book.mbstring.php)


## Getting Started
Usage example of the OpenID Connect (OIDC) authentication:

1. Insert the button embed code on your website. It adds "Proceed with BeautyAList" button on your page. The "client_id" and "redirect_url" parameters should be your client_id (you can find it in your brand dashboard page) and your website-specific redirect_url ( https://website.example/page )

```html
<script type="text/javascript" src="https://beautyalist.com/embed/button/v1.js" async></script>
<a class="blist__openid__button blist__openid__button--default" data-client_id="client_id" data-redirect_url="redirect_url">Proceed with BeautyAList</a>
```

2. Сlicking a button redirects to the specific BeautyAList page where one can register or login to the BeautyAList account.
   As the next step the system will ask permission to share the information with the initiated site. After it the page redirects to the specified redirect_url with code parameter
   ( https://website.example/page?code=one_time_code )

3. Using this library replace one_time_code with permanent access_token to Beautyalist API and get the user information.

```php
$instance = new Beautyalist\API($client_id, $client_secret);
$data = $instance->getToken($one_time_code);
if ($data && !empty($data['access_token'])){
    $user_info = $instance->getUserData($data['access_token']);
    if ($user_info){
        foreach ($user_info as $field => $value) {
          echo $field, " -> ", $value, "<br /> \n";
        }
    }
}
```
