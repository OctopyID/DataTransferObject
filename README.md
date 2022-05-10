<p align="center">
    <img src="https://img.shields.io/packagist/v/octopyid/data-transfer-object.svg?style=for-the-badge" alt="Version">
    <img src="https://img.shields.io/packagist/dt/octopyid/data-transfer-object.svg?style=for-the-badge&color=F28D1A" alt="Downloads">
    <img src="https://img.shields.io/packagist/l/octopyid/data-transfer-object.svg?style=for-the-badge" alt="License">
</p>

# Simple Data Transfer Object (DTO)

A data transfer object (DTO) is an object that carries data between processes. DTO does not have any behaviour except
for storage, retrieval, serialization and deserialization of its own data. DTOs are simple objects that should not
contain any business logic but rather be used for transferring data.

This is DTO version based on my OPINION :)

## Installation

To install the package, simply follow the steps below.

Install the package using Composer:

```
composer require octopyid/data-transfer-object:dev-main
```

## Usage

### Artisan Command

```bash
php artisan make:data UserData
```

This command will generate DTOs from your models.

```bash
php artisan make:data UserData --model=User
```

### Basic DTO

```php
<?php

use Octopy\DTO\DataTransferObject;

class UserData extends DataTransferObject
{
    public function getName() : string
    {
        return $this->get('name');
    }
}

// From Array
$dto = UserData::make([
    'name' => 'John Doe'
]);

// From Request
$dto = UserData::make($request);

// From User Model
$dto = UserData::make($user);

//
echo $dto->getName();
```

## Credits

- [Supian M](https://github.com/SupianIDz)
- [Octopy ID](https://github.com/OctopyID)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
