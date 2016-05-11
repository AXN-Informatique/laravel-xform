# Laravel XForm

Extend Laravel Form tool.

---

**IL EST RECOMMANDÉ DE NE PLUS UTILISER CE PAQUET**

**En effet avec l'expérience, nous nous sommes rendu-compte que ce paquet impose plus de contraintes qu'il ne rend de service. Il enferme dans un cadre qui limite les possibilité de mise en forme.**

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