# TulipAPIBundle

[![Latest version on Packagist][icon-version]][link-version]
[![Software License][icon-license]](LICENSE.md)
[![Build Status][icon-build]][link-build]
[![Coverage Status][icon-coverage]][link-coverage]

Tulip API integration for Symfony 3+.


## Installation using Composer
Run the following command to add the package to the composer.json of your project:

``` bash
$ composer require connectholland/tulip-api-bundle
```

### Enable the bundle
Enable the bundle in the kernel:

``` php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new ConnectHolland\TulipAPIBundle\TulipAPIBundle(),
        // ...
    );
}
```

### Configure the bundle
Add the following configuration to your `config.yml` file:

``` yml
# app/config/config.yml

# Tulip API Configuration
tulip_api:
    url:           %tulip_api_url%
    client_id:     %tulip_api_client_id%
    shared_secret: %tulip_api_shared_secret%
    objects:       ~
```

#### Mapping Doctrine entities to Tulip API services
By default the bundle uses the short name of the entity as service name.
When you need to change this behavior for an entity, you can define a mapping by adding the FQCN to `tulip_api.objects`:

``` yml
# app/config/config.yml

tulip_api:
    objects:
        - {name: AppBundle\Entity\Profile, service: contact}
```


## Usage
...


## Credits
- [Niels Nijens][link-author]

Also see the list of [contributors][link-contributors] who participated in this project.

## License

This package is licensed under the MIT License. Please see the [LICENSE file](LICENSE.md) for details.

[icon-version]: https://img.shields.io/packagist/v/connectholland/tulip-api-bundle.svg
[icon-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[icon-build]: https://travis-ci.org/ConnectHolland/TulipAPIBundle.svg?branch=master
[icon-coverage]: https://coveralls.io/repos/ConnectHolland/TulipAPIBundle/badge.svg?branch=master

[link-version]: https://packagist.org/packages/connectholland/tulip-api-bundle
[link-build]: https://travis-ci.org/ConnectHolland/TulipAPIBundle
[link-coverage]: https://coveralls.io/r/ConnectHolland/TulipAPIBundle?branch=master
[link-author]: https://github.com/niels-nijens
[link-contributors]: ../../contributors
