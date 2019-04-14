Получение токена доступа (сессионного токена) Bitrix24 в автоматическом режиме
================
[![License](https://poser.pugx.org/ujy/bitrix24_api_authorization/license)](https://packagist.org/packages/ujy/bitrix24_api_authorization)
[![Total Downloads](https://poser.pugx.org/ujy/bitrix24_api_authorization/downloads)](https://packagist.org/packages/ujy/bitrix24_api_authorization)
[![Latest Stable Version](https://poser.pugx.org/ujy/bitrix24_api_authorization/v/stable)](https://packagist.org/packages/ujy/bitrix24_api_authorization)
[![Latest Unstable Version](https://poser.pugx.org/ujy/bitrix24_api_authorization/v/unstable)](https://packagist.org/packages/ujy/bitrix24_api_authorization)

##### *Большое спасибо [opravdin](https://github.com/opravdin/) за глобальные правки класса, которые вернули его к рабочему состоянию (после изменений интерфейса логина товарищами из Bitrix24 в феврале 2019)...*
 
#### *Вы можете прочитать этот файл на других языках: [English](README.md), [Русский](README.ru.md).*

Небольшой класс, который поможет сделать процесс авторизации и получение токена доступа к Bitrix24 API намного проще. Определяем основные переменные доступа на Ваш портал битрикс24 и получаете токен в автоматическом режиме.

- Вы можете использовать класс отдельно
- Вы можете использовать класс в связке с библиотекой [mesilov/bitrix24-php-sdk](https://github.com/mesilov/bitrix24-php-sdk) - отличной библиотекой для работы с Bitrix24 API. Собственно именно для удобной работы с этой библиотекой и был написан этот клас.

## Требования
- php: >=5.4
- ext-json: *
- ext-curl: *
- [mesilov/bitrix24-php-sdk](https://github.com/mesilov/bitrix24-php-sdk): опционально 

## Пример работы "как отдельного класса"
``` php
<?php
require_once 'vendor/autoload.php';

$b24auth = new \Bitrix24Authorization\Bitrix24Authorization();

// Заменить на свои данные авторизации
$b24auth->setApplicationId('local.1b1231b1xfa234.12352734'); // Получаем при регистрации приложения на Вашем портале Bitrix24
$b24auth->setApplicationSecret('q3vOqweJSasd1wDAkdL3qq13rqKDe8ffGMlFsI8Ykpasld4n0w'); // Получаем при регистрации приложения на Вашем портале Bitrix24
$b24auth->setApplicationScope('crm,user,telephony'); // Перечисляем объекты Bitrix24 с которыми хотим работать, в приложении они также должны быть выбраны
$b24auth->setBitrix24Domain('your-site.bitrix24.ru'); // Адрес Вашего портала Bitrix24
$b24auth->setBitrix24Login('your_login@gmail.com'); // Логин реального пользователя Вашего портала Bitrix24
$b24auth->setBitrix24Password('your_password'); // Пароль реального пользователя Вашего портала Bitrix24

// Инициализация
$b24auth->initialize();

// теперь можно использовать объект $b24auth для дальнейшей работы с API
var_dump($b24auth->bitrix24_access); // Здесь находятся Ваши данные авторизации и токен после инициализации. Токен действителен 1 час
// Или Вы просто можете использовать $b24auth->initialize() каждый раз - метод не будет перегенерировать токен авторизации пока тот не будет просрочен. Отдаваемое содержимое будет аналогичным свойству "bitrix24_access"
?>
```
#### Вызов предыдущего кода выведет следующее (если Вы заменили данные авторизации на свои):
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
## Пример использования "как часть библиотеки mesilov/bitrix24-php-sdk"
``` php
<?php
require_once 'vendor/autoload.php';

// Инициализация оъекта библиотеки mesilov/bitrix24-php-sdk
$mesilov_obj = new \Bitrix24\Bitrix24();

// Инициализация класса авторизации
$b24auth = new \Bitrix24Authorization\Bitrix24Authorization();

// Заменить на свои данные авторизации
$b24auth->setApplicationId('local.1b1231b1xfa234.12352734'); // Получаем при регистрации приложения на Вашем портале Bitrix24
$b24auth->setApplicationSecret('q3vOqweJSasd1wDAkdL3qq13rqKDe8ffGMlFsI8Ykpasld4n0w'); // Получаем при регистрации приложения на Вашем портале Bitrix24
$b24auth->setApplicationScope('crm,user,telephony'); // Перечисляем объекты Bitrix24 с которыми хотим работать, в приложении они также должны быть выбраны
$b24auth->setBitrix24Domain('your-site.bitrix24.ru'); // Адрес Вашего портала Bitrix24
$b24auth->setBitrix24Login('your_login@gmail.com'); // Логин реального пользователя Вашего портала Bitrix24
$b24auth->setBitrix24Password('your_password'); // Пароль реального пользователя Вашего портала Bitrix24

// Инициализация
$mesilov_auth_obj = $b24auth->initialize($mesilov_obj);
/* Во время инициализации с передачей объекта происходит вызов следующих методов из библиотеки mesilov/bitrix24-php-sdk:
    $mesilov_obj->setApplicationScope();
    $mesilov_obj->setApplicationId();
    $mesilov_obj->setApplicationSecret();
    $mesilov_obj->setDomain();
    $mesilov_obj->setMemberId();
    $mesilov_obj->setAccessToken();
    $mesilov_obj->setRefreshToken();
*/

// дальше работаем с Bitrix24 API
// НАПРИМЕР: Получение информации о текущем пользователе используя библиотеку и данные авторизации полученные в автоматическом режиме
$obB24User = new \Bitrix24\User\User($mesilov_auth_obj);
$arCurrentB24User = $obB24User->current();

var_dump($arCurrentB24User);
```
## Установка ##
Добавьте `"ujy/bitrix24_api_authorization": "dev-master"` в `composer.json` Вашего приложения. Или клонируйте репозиторий и подключите к проекту.

Или просто используйте команду composer `"composer require ujy/bitrix24_api_authorization:dev-master`

## Баги и предложения
Баги и предложения оставляйте на странице  [GitHub Issues](https://github.com/xUJYx/bitrix24_api_authorization/issues)

## Лицензия
Класс распространяется по лицензии MIT - более детально читайте в файле [LICENSE](LICENSE)

## Автор
Евгений Гардыш - <ujy@ukr.net> / <ghenia.hard@gmail.com><br />
Олег Правдин (обновление класса после изменения UI bitrix24) - <opravdin@gmail.com><br /><br />
Также выражаю благодарность Сергею  [afinogen.su](https://afinogen.su/) за базовую версию скрипта получения токена авторизации в автоматическом режиме.

## Остались вопросы? ##
Направляйте их сюда: <br />
<ujy@ukr.net> / <ghenia.hard@gmail.com><br />
<opravdin@gmail.com><br />