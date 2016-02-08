# Horne

A static page generator for HTML



## Install

### Standalone version

After cloning, run [Composer](https://getcomposer.org/) to install Horne’s
dependencies. You might also need to set the file `horne` as executable.

A full example install script:

~~~ bash
git clone https://github.com/mermshaus/horne.git
cd horne
composer install
chmod +x horne
./horne --version
~~~

### As a dependency via Composer

~~~ json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/mermshaus/horne"
        }
    ],
    "require": {
        "geshi/geshi": "dev-master",
        "mermshaus/horne": "~0.3"
    }
}
~~~

Sorry, no `$ composer require` yet. Run with `$ ./vendor/bin/horne --version`.



## Building bundled demo projects

From Horne’s root directory run:

~~~ bash
./horne build --working-dir demos/hello-world
./horne build --working-dir demos/blog
~~~

The HTML output will be written to `./demos/hello-world/output` and
`./demos/blog/output`. You should be able to open the respective `index.html`
files with a browser to view the result. Take a look at the contents of the
project directories in `./demos` to get an idea what the output is based on.



## API

You have access to the following methods in `*.phtml` files via the `$api`
variable.

- `getPathToRoot() : string`

  Returns the relative path to the project’s root directory (without
  trailing slash).

- `getMetasByType(string $type, array $order = array(), int $limitCount = -1, int $limitOffset = 0) : array`

  Retrieves a set of `MetaBag` instances specified by certain criteria.

- `e(string $s) : string`

  Escapes strings for insertion into HTML code. Use extensively.

- `datef(string $date) : string`

  Reformats a date (`Y-m-d H:i:s`) in a nicer way. This method will probably
  be deprecated.

- `getSetting(string $key) : mixed`

  Returns a config value (e. g. from `_horne.json`) by dot-separated key.
  The `modules` index may be omitted. `blog.useTags` is interpreted as
  `modules.blog.useTags`.

- `url(string $id) : string`

  Returns a link to a Horne resource. Use for internal links.

- `render(string $id, array $vars = array()) : string`

  Generates and returns the output of a Horne resource for the given
  parameters. Useful to avoid redundancy.

- `getAllMetas() : array`

  Returns all MetaBags known to the Horne instance. Use this where
  `getMetasByType` is too limited.

- `getModule(string $id) : Horne\Module`

  Gives access to module instances.

- `getMetasByTag(string $tag) : array`

  Retrieves all Horne resources (`MetaBag`) with a specific tag. Might be
  deprecated.

- `getMetaById(string $id) : Horne\MetaBag`

  Retrieves a specific MetaBag by Horne resource id.

- `syntax(string $source, string $lang = 'text') : string`

  Returns a syntax-highlighted string. The PHTML renderer uses GeSHi for
  syntax highlighting.



## Modules

(This list is incomplete.)

- Blog
  - `bool showArticleCounter = true`
  - `bool showAuthor = true`
  - `bool showInfoline = true`
  - `bool useTags = true`
- Debug
- Linkblog
  - `string dataFile = "linkblog.rss"`
  - `int    entriesPerPage = 40` (-1 for no limit)
- System
  - `string siteName = null`
  - `string siteSlogan = null`
- Theme
  - `string name`



## License

The MIT License (MIT). See LICENSE for more information.
