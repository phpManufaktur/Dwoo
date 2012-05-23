### Dwoo

Implementation of the [Dwoo template engine] [4] for WebsiteBaker and LEPTON CMS

#### Requirements

* minimum PHP 5.2.x
* using [WebsiteBaker] [1]
* _or_ using [LEPTON CMS] [2]

#### Installation

* download the actual [Dwoo_x.xx.zip] [3] installation archive
* in CMS backend select the file from "Add-ons" -> "Modules" -> "Install module"

#### First Steps

You can access the *Dwoo* template engine from your own addons:

    if (!class_exists('Dwoo')) {
      require_once WB_PATH.'/modules/dwoo/include.php';
    }
    // set cache and compile path for the template engine
    $cache_path = WB_PATH.'/temp/cache';
    if (!file_exists($cache_path)) 
      mkdir($cache_path, 0755, true);
    $compiled_path = WB_PATH.'/temp/compiled';
    if (!file_exists($compiled_path)) 
      mkdir($compiled_path, 0755, true);

    // init the template engine
    global $parser;
    if (!is_object($parser)) 
      $parser = new Dwoo($compiled_path, $cache_path);

that's all.

Please visit the [Dwoo Homepage] [4] to get more informations about the usage of this template engine.  

[1]: http://websitebaker2.org "WebsiteBaker Content Management System"
[2]: http://lepton-cms.org "LEPTON CMS"
[3]: https://github.com/phpManufaktur/Dwoo/downloads
[4]: http://dwoo.org
