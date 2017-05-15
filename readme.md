# Laravel XForm

Extend Laravel Form tool.

---

***DEPRECATED***

**IL EST RECOMMANDÉ DE NE PLUS UTILISER CE PAQUET**

**En effet avec l'expérience, nous nous sommes rendu-compte que ce paquet impose plus de contraintes qu'il ne rend de service.
Il enferme dans un cadre rigide qui limite les possibilités de mise en forme et cela est très frustrant.**

---

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
