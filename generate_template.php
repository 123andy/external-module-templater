<?php

namespace RedcapConHack\Templater;
use ExternalModules\ExternalModules;
use Twig\Extension\StringLoaderExtension;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/** @var Templater $module */

require_once(__DIR__ . "/vendor/autoload.php");

$loader = new FilesystemLoader(__DIR__ . "/templates/");
$twig = new Environment($loader);
$twig->addExtension(new StringLoaderExtension());

if ($_POST) {
    $user_cache=[];
    foreach ($_POST as $k => $v) {
        if ($k == "orgName" || $k == "gitOrg" || $k == "gitTeam" || left($k,7) == "include" || left($k,7) == "authors") $user_cache[$k] = $v;
    }
    // file_put_contents('/var/log/redcap/dump.json', json_encode($user_cache));
    $module->setUserSetting('defaults', $user_cache);
    $module->generateTemplateFromPost($twig);
} else {
    ?>

    <script>
        const timezone = "<?php echo date_default_timezone_get(); ?>";
        const timestamp = "<?php echo date("Y-m-d H:i:s"); ?>";
    </script>

    <?php
    $templateConstants = [
        "icon_url" => APP_PATH_WEBROOT . "Resources/images/favicon.ico",
        "js_link" => APP_PATH_JS . "base.js",
        "js_link2" => $module->getUrl('js/functions.js'),
        "css_link" => APP_PATH_CSS . "jquery-ui-min.css",
        "css_link2" => APP_PATH_CSS . "bootstrap.min.css",
        "hooks" => $module::getHookInfo(),
        "default_module_version" => $module::DEFAULT_MODULE_VERSION,
        "default_framework_version" => ExternalModules::getMaxSupportedFrameworkVersion(),  // $module::DEFAULT_FRAMEWORK_VERSION,
        "framework_doc_url" => APP_PATH_WEBROOT . "Plugins/index.php?page=ext_mods_docs/framework/intro.md",
        "user_cache" => json_encode($module->getUserSetting('defaults')),
        "redcap_csrf_token" => $module->getCSRFToken()
    ];

    ## Checking if on newer version of REDCap that uses webpack for jquery and bootstrap
    if (!is_file(APP_PATH_DOCROOT . "Resources/css/jquery-ui.min.css")) {
        $templateConstants["js_link"] = APP_PATH_WEBPACK . "js/bundle.js";
        $templateConstants["css_link"] = APP_PATH_WEBPACK . "css/bundle.css";
        $templateConstants["css_link2"] = false;
    }
    echo $twig->render("newModule.twig", $templateConstants);
}

// Prevent js errors about missing variables
renderJsVars();
