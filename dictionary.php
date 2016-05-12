<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=standalone
[END_COT_EXT]
==================== */

/**
 * Dictionary plugin for Cotonti
 *
 * @package Dictionary
 * @author Kalnov Alexey <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
defined('COT_CODE') or die('Wrong URL.');

require_once cot_incfile('dictionary', 'plug');
require_once cot_langfile('dictionary', 'plug');

require_once cot_incfile('page', 'module');

// Роутер
// Only if the file exists...
if (!$m) $m = 'main';

if (file_exists(cot_incfile($env['ext'], 'plug', $m))) {
    require_once cot_incfile($env['ext'], 'plug', $m);
    /* Create the controller */
    $_class = ucfirst($m).'Controller';
    $controller = new $_class();

    // TODO кеширование
    /* Perform the Request task */
    $currentAction = $a.'Action';
    if (!$a && method_exists($controller, 'indexAction')){
        $content = $controller->indexAction();
    }elseif (method_exists($controller, $currentAction)){
        $content = $controller->$currentAction();
    }else{
        // Error page
        cot_die_message(404);
        exit;
    }


    //ob_clean();
    // todo дописать как вывод для плагинов
//    require_once $cfg['system_dir'] . '/header.php';
//    if (isset($content)) echo $content;
//    require_once $cfg['system_dir'] . '/footer.php';
}else{
    // Error page
    cot_die_message(404);
    exit;
}
