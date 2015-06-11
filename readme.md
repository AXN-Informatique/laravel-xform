# Laravel XForm

Extend Laravel Form tool.

## Installation

Require this package in your composer.json:

```
    "require" : {
        "axn/laravel-xform" : "~2.0"
    }
```

Add private repository to your composer.json:

```
    "repositories" : [{
            "type" : "vcs",
            "url" : "git@bitbucket.org:axn/laravel-xform.git"
        }
    ]
```

You need a SSH key to run the next command:

```
composer update
```

After updating composer, add the ServiceProvider to the providers array in config/app.php

```
'Axn\LaravelXForm\XFormServiceProvider',
```

Add this to your facades in config/app.php:

```
'XForm' => 'Axn\LaravelXForm\Facades\XForm',
```
