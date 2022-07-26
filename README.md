<p align="center">
    <a href="https://github.com/osCommerce" target="_blank">
        <img src="https://www.oscommerce.com/images/blog/logo%20osC.webp" height="100px">
    </a>
    <h1 align="center">osCommerce v4</h1>
    <br>
</p>

[osCommerce](https://www.oscommerce.com/)  is a Free shopping cart and open source Ecommerce software.
Based  on  the  latest  versions  of  PHP  and mySQL, it is one of the fastest  shopping  cart  solutions  on  the market. 

osCommerce can be used by SMEs and global corporations, as it has features and simplicity to satisfy all users.

It comes full of built in features, and a number of additional plugins.

USPs include:
- multiple front ends
- themes
- visual theme designer
- responsive design
- full site control via the CMS
- SEO friendly
- support for configurable products
- advanced product management and pricing

Additional plugins are available:
- Amazon and eBay integration
- accounting software integration (Sage, QuickBooks, Exact, MYOB)
- visual product designer 

REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 7.2.0.


INSTALLATION
------------

### install via git

You can then install this project using the following command from directory (e.g. `oscommerce`):

~~~
git clone https://github.com/osCommerce/osCommerce-V4.git ./ 
~~~

Make sure your web user has write permissions to the following directories:

```
assets                   contains application assets such as JavaScript and CSS
admin
    assets/              contains application assets such as JavaScript and CSS
lib
    backend
        runtime/             contains files generated during runtime
    console
        runtime/             contains files generated during runtime
    frontend
        runtime/             contains files generated during runtime
themes                   contains application assets such as JavaScript and CSS
```

Continue local installation

Now you should be able to access the application through the following URL, assuming `oscommerce` is the directory
directly under the Web root.

~~~
http://localhost/oscommerce/
~~~

### Install from an Archive File

Extract the archive file downloaded from [www.oscommerce.com](https://www.oscommerce.com/download-oscommerce) to
a directory named `oscommerce` that is directly under the Web root.

You can then access the application through the following URL:

~~~
http://localhost/oscommerce/
~~~

**NOTES:**
- osCommerce won't create the database for you, this has to be done manually before you can access it.
- Make sure you have permissions to write before installation.
- Do't forget delete subdirectory `install` after installation.
