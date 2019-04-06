Get access token for Bitrix24 API in automatic way
================
[![License](https://poser.pugx.org/ujy/bitrix24_api_authorization/license)](https://packagist.org/packages/ujy/bitrix24_api_authorization)
[![Total Downloads](https://poser.pugx.org/ujy/bitrix24_api_authorization/downloads)](https://packagist.org/packages/ujy/bitrix24_api_authorization)
[![Latest Stable Version](https://poser.pugx.org/ujy/bitrix24_api_authorization/v/stable)](https://packagist.org/packages/ujy/bitrix24_api_authorization)
[![Latest Unstable Version](https://poser.pugx.org/ujy/bitrix24_api_authorization/v/unstable)](https://packagist.org/packages/ujy/bitrix24_api_authorization)

##### *Big thanks to [opravdin](https://github.com/opravdin/) for the global class FIX which returned class back to life after Bitrix24 team changed their login page UI in February 2019...*

#### *Read this document on other languages: [English](README.md), [Русский](README.ru.md).*

Tiny class to make Bitrix24 authorization process automated. Just define few vars and get Bitrix24 access token (session token) in automatic way.

- You can use it as separated class
- You can use it in pair with [mesilov/bitrix24-php-sdk](https://github.com/mesilov/bitrix24-php-sdk) - great lib for work with Bitrix24 API.

## Requirements
- php: >=5.4
- ext-json: *
- ext-curl: *
- [mesilov/bitrix24-php-sdk](https://github.com/mesilov/bitrix24-php-sdk): optional 

## Example "as separated class"
``` php
<?php
require_once 'vendor/autoload.php';

$b24auth = new \Bitrix24Authorization\Bitrix24Authorization();

// Change example data to your own
$b24auth->setApplicationId('local.1b1231b1xfa234.12352734'); // Getting when registring Bitrix24 application
$b24auth->setApplicationSecret('q3vOqweJSasd1wDAkdL3qq13rqKDe8ffGMlFsI8Ykpasld4n0w'); // Getting when registring Bitrix24 application
$b24auth->setApplicationScope('crm,user,telephony'); // write Bitrix24 instances which you want to use via API. They need to be choosen in application at Bitrix24
$b24auth->setBitrix24Domain('your-site.bitrix24.ru'); // Address of your Bitrix24 portal
$b24auth->setBitrix24Login('your_login@gmail.com'); // login of your real user, he need to be an Admibistrator of instance you want to use
$b24auth->setBitrix24Password('your_password'); // password of your real user, he need to be an Admibistrator of instance you want to use

$b24auth->initialize();

// Now you can use object $b24auth

var_dump($b24auth->bitrix24_access); // Here you`ve got your authorization data
// Or you can just use $b24auth->initialize() - it will return the same data as property "bitrix24_access". Access token will not RE generated until it expires
?>
```
#### Output will be like this:
```
object(stdClass)#2 (11) {
  ["access_token"]=>
  string(70) "d69d585b0027431000005e120000011c000003b69bb7c48c33547a3b053d21a5c1d0f5"
  ["expires"]=>
  int(1532534230)
  ["expires_in"]=>
  int(3600)
  ["scope"]=>
  string(18) "crm,user,telephony"
  ["domain"]=>
  string(18) "your-site.bitrix24.ru"
  ["server_endpoint"]=>
  string(31) "https://oauth.bitrix.info/rest/"
  ["status"]=>
  string(1) "L"
  ["client_endpoint"]=>
  string(32) "https://your-site.bitrix24.ru/rest/"
  ["member_id"]=>
  string(32) "02f2213319a5e74fb12351c15345fe81"
  ["user_id"]=>
  int(1)
  ["refresh_token"]=>
  string(70) "c61c805b0027431000005e120000011c000003e02d1b4a9ce34e617d09cecbf6e69efb"
}
```
## Example "as part of mesilov/bitrix24-php-sdk library"
``` php
<?php
require_once 'vendor/autoload.php';

// Initializing object from mesilov/bitrix24-php-sdk library
$mesilov_obj = new \Bitrix24\Bitrix24();

// Initializing authorization class
$b24auth = new \Bitrix24Authorization\Bitrix24Authorization();

// Change example data to your own
$b24auth->setApplicationId('local.1b1231b1xfa234.12352734'); // Getting when registring Bitrix24 application
$b24auth->setApplicationSecret('q3vOqweJSasd1wDAkdL3qq13rqKDe8ffGMlFsI8Ykpasld4n0w'); // Getting when registring Bitrix24 application
$b24auth->setApplicationScope('crm,user,telephony'); // write Bitrix24 instances which you want to use via API. They need to be choosen in application at Bitrix24
$b24auth->setBitrix24Domain('your-site.bitrix24.ru'); // Address of your Bitrix24 portal
$b24auth->setBitrix24Login('your_login@gmail.com'); // login of your real user, he need to be an Admibistrator of instance you want to use
$b24auth->setBitrix24Password('your_password'); // password of your real user, he need to be an Admibistrator of instance you want to use

// Initializing
$mesilov_auth_obj = $b24auth->initialize($mesilov_obj);
/* Initializing with mesilov object as parameter call next methods of mesilov/bitrix24-php-sdk library:
    $mesilov_obj->setApplicationScope();
    $mesilov_obj->setApplicationId();
    $mesilov_obj->setApplicationSecret();
    $mesilov_obj->setDomain();
    $mesilov_obj->setMemberId();
    $mesilov_obj->setAccessToken();
    $mesilov_obj->setRefreshToken();
*/

// Now we can work with Bitrix24 API using library
// EXAMPLE: Lets get info about current user, who was used in authorization process
$obB24User = new \Bitrix24\User\User($mesilov_auth_obj);
$arCurrentB24User = $obB24User->current();

var_dump($arCurrentB24User);
```
## Installation ##
Add `"ujy/bitrix24_api_authorization": "dev-master"` to `composer.json` of your application. Or clone repo to your project.

Or just use composer command `"composer require ujy/bitrix24_api_authorization:dev-master`

## Submitting bugs and feature requests
Bugs and feature request are tracked on [GitHub](https://github.com/xUJYx/bitrix24_api_authorization/issues)

## License
Current class is licensed under the MIT License - see the [LICENSE](LICENSE) file for details

## Author
Ievgenii Gardysh - <ujy@ukr.net> / <ghenia.hard@gmail.com><br />
Oleg Pravdin (class rewriter after bitrix UI update) - <opravdin@gmail.com><br />

Thanks to Sergey from [afinogen.su](https://afinogen.su/) for base version of script which gets access token in automated way.

## Have any questions? ##
email: <ujy@ukr.net> / <ghenia.hard@gmail.com><br />
email: opravdin@gmail.com