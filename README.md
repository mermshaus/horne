Horne
=====

A static page generator for HTML



Installation
------------

After cloning, run [Composer](https://getcomposer.org/) to install Horne’s
dependencies. You might also need to set the file `horne` as executable.

A full example install script:

~~~
git clone https://github.com/mermshaus/horne.git
cd horne
curl -s http://getcomposer.org/installer | php
php composer.phar install
chmod +x horne
./horne --version
~~~



Building bundled demo projects
------------------------------

From Horne’s root directory run:

~~~
./horne build --working-dir demos/hello-world
./horne build --working-dir demos/blog
~~~

The HTML output will be written to `./demos/hello-world/output` and
`./demos/blog/output`. You should be able to open the respective `index.html`
files with a browser to view the result. Take a look at the contents of the
project directories in `./demos` to get an idea what the output is based on.



API
---

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

  Retrieves all Horne resources (`MetaBag`) with a specific set. Might be
  deprecated.

- `getMetaById(string $id) : Horne\MetaBag`

  Retrieves a specific MetaBag by Horne resource id.

- `syntax(string $source, string $lang) : string`

  Returns a syntax-highlighted string. The PHTML renderer uses GeSHi for
  syntax highlighting.



License
-------

Horne is licensed under the GPLv3 license. (See COPYING.)

~~~
Copyright (C) 2013  Marc Ermshaus

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.
~~~
