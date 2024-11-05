# Library for easier work with external filters

Encapsulation of a command along with its options which provides a filter method.
Its primary role is to **facilitate filtering operations within a pipeline**,
allowing for easy chaining and execution of executable filters.

```php
namespace PetrKnap\ExternalFilter;

# echo "H4sIAAAAAAAAA0tJLEkEAGPz860EAAAA" | base64 --decode | gzip --decompress
echo (
    new Filter('base64', ['--decode'])
)->pipe(
    new Filter('gzip', ['--decompress'])
)->filter('H4sIAAAAAAAAA0tJLEkEAGPz860EAAAA');
```

---

Run `composer require petrknap/external-filter` to install it.
You can [support this project via donation](https://petrknap.github.io/donate.html).
The project is licensed under [the terms of the `LGPL-3.0-or-later`](./COPYING.LESSER).
