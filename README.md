# insales_api.php
#
PHP библиотека для работы с InsalesApi [insales.ru](http://www.insales.ru)


## Системные требования

* PHP 5.3 с [поддержкой cURL](http://php.net/manual/en/book.curl.php).

### Использование
Выполнение вызовов к API:

```php
<?php

	$insales_api = insales_api_client($my_insales_domain, $api_key, $password);

	try
	{
		// Получить все заказы
		$orders = $insales_api('GET', '/admin/orders.json');

		// Добавить новый виджет
		$widget = array
		(
			"application_widget"=>array
			(
        "code"   => 'some widget code',
        "height" => 60
			)
		);

    $response = $insales_api('POST', '/admin/application_widgets.json', $widget);
	}
	catch (InsalesApiException $e)
	{
		/* $e->getInfo() вернет массив со следующими ключами:
			* method
			* path
			* params (third parameter passed to $shopify)
			* response_headers
			* response
			* shops_myshopify_domain
			* shops_token
		*/
	}
	catch (InsalesCurlException $e)
	{
		// $e->getMessage() возвращает содержимое curl_errno(), $e->getCode() возвращает содержимое curl_ error()
	}
?>
```
