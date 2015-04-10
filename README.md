Yii 2 credit card validation
============================
credit card validation yii 2

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist bryglen/yii2-validators "1.0.1"
```

or add

```
"bryglen/yii2-validator": "1.0.1"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your models :

```php
public function rules()
{
    return [
        ['cc', 'bryglen\validators\CreditCardValidator']
    ];
}
```