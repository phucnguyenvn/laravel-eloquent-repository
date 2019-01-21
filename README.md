# Eloquent Repository
Package to assist the implementation of the Repository Pattern using Eloquent ORM.

## Installation
Installing via Composer
```bash
composer require phucnguyenvn/laravel-eloquent-repository
```
To configure the package options, declare the Service Provider in the `config/app.php` file.
```php
'providers' => [
    ...
    phucnguyenvn\EloquentRepository\Providers\RepositoryServiceProvider::class,
],
```
> If you are using version 5.5 or higher of Laravel, the Service Provider is automatically recognized by Package Discover.

### Usage
To get started you need to create your repository class and extend the `EloquentRepository` available in the package. You also have to set the *Model* that will be used to perform the queries.

Example:
```php
namespace App\Repositories;

use App\Models\User;
use phucnguyenvn\EloquentRepository\Repositories\EloquentRepository;

class UserRepository extends EloquentRepository
{
    /**
     * Repository constructor.
     */
    public function __construct(User $user){
        parent::__construct($user);
    }

    protected function model() {
        return \App\User::class;
    }
}
```

Now it is possible to perform queries in the same way as it is used in Eloquent.
```php
namespace App\Repositories;

use App\Models\User;
use phucnguyenvn\EloquentRepository\Repositories\EloquentRepository;

class UserRepository extends EloquentRepository
{
    /**
     * Repository constructor.
     */
    public function __construct(User $user){
        parent::__construct($user);
    }

    protected function model() {
        return \App\User::class;
    }
    
    public function getAllUser(){
        return $this->all();
    }
    
    public function getByName($name) {
        return $this->where("name", $name)->get();
    }
    
    // You can create methods with partial queries
    public function filterByProfile($profile) {
        return $this->where("profile", $profile);
    }
    
    // Them you can use the partial queries into your repositories
    public function getAdmins() {
        return $this->filterByProfile("admin")->get();
    }
    public function getEditors() {
        return $this->filterByProfile("editor")->get();
    }
    
    // You can also use Eager Loading in queries
    public function getWithPosts() {
        return $this->with("posts")->get();
    }
}
```

To use the class, just inject them into the controllers.
```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    protected function index(UserRepository $repository) {
        return $repository->getAdmins();
    }
}
```
The injection can also be done in the constructor to use the repository in all methods.
```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
class UserController extends Controller
{
    private $userRepository;
	public function __construct()(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }
    
    public function index() {
        return $this->userRepository->getAllUsers();
    }
    
}
```
> The Eloquent/QueryBuilder methods are encapsulated as protected and are available just into the repository class. Declare your own public data access methods within the repository to access them through the controller.

