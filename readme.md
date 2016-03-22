# Laravel XForm

Extend Laravel Form tool.

## Installation

Install the package with Composer :

```
composer require axn/laravel-xform
```

Add the ServiceProvider to the providers array in config/app.php

```
'Axn\LaravelXForm\XFormServiceProvider',
```

Add this to your facades in config/app.php:

```
'XForm' => 'Axn\LaravelXForm\Facades\XForm',
```
