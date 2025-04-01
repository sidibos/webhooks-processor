<?php
namespace Webhook;

set_include_path (".");

spl_autoload_register(function ($className) {

    $filename = 'src/' . get_include_path()."/".$className . '.php';
    $filename = str_replace("\\","/",$filename);

    if(!file_exists( $filename ) ) {
        die("file not found" . $filename);
    }
    require_once $filename;
});
