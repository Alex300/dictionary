<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=tools
[END_COT_EXT]
==================== */
/**
 * Dictionary plugin for Cotonti
 *
 * @package Dictionary
 * @author Kalnov Alexey <kalnovalexey@yandex.ru>
 * @copyright Portal30 Studio http://portal30.ru
 */
(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = cot_auth('plug', $env['ext'], 'RWA');
cot_block($usr['isadmin']);

// Self requirements
require_once cot_incfile($env['ext'], 'plug');

// Стандартный Роутер
// Only if the file exists...
if (!$n) $n = 'main';

if (file_exists(cot_incfile($env['ext'], 'plug', 'admin.'.$n))) {
    require_once cot_incfile($env['ext'], 'plug', 'admin.'.$n);
    /* Create the controller */
    $_class = 'Admin'.ucfirst($n).'Controller';
    $controller = new $_class();

    if(!$a) $a = cot_import('a', 'P', 'TXT');

    /* Perform the Request task */
    $currentAction = $a.'Action';
    if ($a && method_exists($controller, $currentAction)) {
        $outContent = $controller->$currentAction();
    } elseif(empty($a) && method_exists($controller, 'indexAction')) {
        $outContent = $controller->indexAction();
    } else {
        // Error page
        cot_die_message(404);
        exit;
    }

    // todo дописать как вывод для плагинов
    if (isset($outContent)){
        $plugin_body .= $outContent;
        unset($outContent);
    }

}else{
    // Error page
    cot_die_message(404);
    exit;
}

