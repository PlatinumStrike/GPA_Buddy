<?php
function adminer_object()
{
    // required to run any plugin
    include_once "plugins/plugin.php";

    // autoloader
    foreach (glob("adminer/plugins/*.php") as $filename) {
        include_once "./$filename";
    }

    // enable extra drivers just by including them
    //~ include "./plugins/drivers/simpledb.php";

    $plugins = array(
        // specify enabled plugins here
        // new AdminerDumpXml(),
        new AdminerDatabaseHide(["information_schema", "mysql", "performance_schema"]),
        new AdminerEnumOption(),
        new AdminerEnumTypes()
    );

    /* It is possible to combine customization and plugins:
    class AdminerCustomization extends AdminerPlugin {
    }
    return new AdminerCustomization($plugins);
    */

    return new AdminerPlugin($plugins);
}

// include original Adminer or Adminer Editor
include "adminer-4.8.4.php";