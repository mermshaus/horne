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
