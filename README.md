genealogy
==========

`genealogy` is a simple web interface for visualising family trees and genealogies using YML.

# Features

1. Coffeescript is compiled into Javascript (from `site/js/`)
2. SCSS is compiled into CSS (from `site/css/`)
3. HAML is compiled into HTML using templates (from `site/templates/`)
4. CSS images are spritified using [spritify](https://github.com/soundasleep/spritify)

# Installing

1. [Fork this project](https://github.com/soundasleep/genealogy) or
   [download the latest .zip](https://github.com/soundasleep/genealogy/archive/master.zip).
2. Run `npm install` and `composer update`
3. Run `grunt` to compile the assets. (You can also use `grunt serve` to watch for changes.)

# Using

Create your own family tree in `tree/family.yml`, for example:

> TODO add example.yml

# Deploying

## Apache 2.4

```conf
Alias "/genealogy" "/var/www/genealogy/site"
<Directory "/var/www/genealogy/site">
  Options Indexes FollowSymLinks
  DirectoryIndex index.html index.php default.html default.php
  AllowOverride All
  Allow from All
  Require all granted
</Directory>
```

# TODO

