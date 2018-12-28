# phalcon_mvc_framwork

```php

├── apps
│   ├── models
│   │   ├── entities
│   │   │   └── User.php
│   │   ├── repositories
│   │   │   ├── Exceptions
│   │   │   │   └── InvalidRepositoryException.php
│   │   │   ├── Repositories.php
│   │   │   └── Repository
│   │   │       └── User.php
│   │   └── services
│   │       ├── Exceptions
│   │       │   └── InvalidServiceException.php
│   │       ├── Service
│   │       │   └── User.php
│   │       └── Services.php
│   └── modules
│       └── frontend
│           ├── bootstrap
│           │   ├── services.php
│           │   ├── loader.php
│           │   └── router.php      
│           ├── Module.php
│           ├── controllers
│           │   ├── ControllerBase.php
│           │   └── IndexController.php
│           └── views
│               ├── index
│               │   └── index.phtml
│               └── index.phtml
├── config
│   ├── local
│   ├── dev
│   ├── test
│   ├── online
│   └── config.php
├── docs
│    └── database.sql
├── public
│   └── index.php
├───tests
│    ├── Services
│    │   └── UserServiceTest.php
│    ├── TestHelper.php
│    ├── UnitTestCase.php
│    └── phpunit.xml
│
├── vendor
│   ├── library
│   │   ├──Loger.php
│   │   └── ...
│   │
│   └── ...
```
