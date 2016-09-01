<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 * @package Mitgliederliste RSZ
 * @link    http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

// frontend modules
array_insert($GLOBALS['FE_MOD'], 0, array(
        'orientierungslauf' => array(
            'markocupic_orientierungslauf_app' => 'Markocupic\OrientierungslaufApp',
        ),
    ));


/**
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD'], 0, array(
    // Content modules
    'orientierungslaeufe' => array(
        'course' => array(
            'tables' => array('tl_track', 'tl_position', 'tl_race', 'tl_run'),
            //'table'       => array('TableWizard', 'importTable'),
            //'list'        => array('ListWizard', 'importList')
        )

    )
));

if(TL_MODE == 'BE' && Input::get('do') == 'course'){
    $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/mcupic_orientierungslauf_app/public/be.js';
}
if(TL_MODE == 'FE'){
    $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/mcupic_orientierungslauf_app/public/fe.js';
}

