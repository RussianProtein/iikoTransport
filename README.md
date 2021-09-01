# iikoTransport/iikoCloud
iikoTransport/iikoCloud API Library for Laravel Framework

## Overview

This package contains integration with iikoTransport/iikoCloud

The package is based on the documentation [`https://api-ru.iiko.services/`](https://api-ru.iiko.services/).

## Installation

```
composer require russianprotein/iikotransport
php artisan vendor:publish
```


Select 

```Provider: RussianProtein\iikoTransport\ServiceProvider```

## Usage


Example

```
<?php

namespace App\Http\Controllers;

use RussianProtein\iikoTransport\iikoTransport;

class Controller extends BaseController
{
    public function index()
    {
        //Call class iikoTransport (If we do not pass anything to the class, then the default parameter is used from env IIKO_API_LOGIN)
        $data = new iikoTransport('set iiko login');

        //get Organization List 
        $organisations = $data->getOrganizations(null, true, true);
    }
}
```
