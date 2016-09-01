<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Load class tl_page
 */
$this->loadDataContainer('tl_page');


/**
 * Table tl_track
 */
$GLOBALS['TL_DCA']['tl_track'] = array(

    // Config
    'config'      => array(
        'dataContainer'    => 'Table',
        'ctable'           => array('tl_position'),
        'switchToEdit'     => true,
        'enableVersioning' => true,
        'onload_callback'  => array(//
        ),
        'sql'              => array(
            'keys' => array(
                'id' => 'primary',
            ),
        ),
    ),
    // List
    'list'        => array(
        'sorting'           => array(
            'mode'   => 1,
            'fields' => array('title'),
            'flag'   => 1,
        ),
        'label'             => array(
            'fields' => array('title'),
            'format' => '<strong>Track:</strong> %s',
        ),
        'global_operations' => array(
            'all' => array(
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ),
        ),
        'operations'        => array(

            'editheader' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_track']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'header.gif',
                //'button_callback'     => array('tl_track', 'editHeader')
            ),
            'add_positions'       => array(
                'label' => &$GLOBALS['TL_LANG']['tl_track']['add_positions'],
                'href'  => 'table=tl_position',
                'icon'  => 'system/modules/mcupic_orientierungslauf_app/public/flag_blue.png',
            ),
            'addRaceEvent'       => array(
                'label' => &$GLOBALS['TL_LANG']['tl_track']['addRaceEvent'],
                'href'  => 'table=tl_race',
                'icon'  => 'system/modules/mcupic_orientierungslauf_app/public/medal_gold_add.png',
            ),
            'copy'       => array(
                'label' => &$GLOBALS['TL_LANG']['tl_track']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ),
            'delete'     => array(
                'label'      => &$GLOBALS['TL_LANG']['tl_track']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ),
            'show'       => array(
                'label' => &$GLOBALS['TL_LANG']['tl_track']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ),
            'toggle'     => array(
                'label'           => &$GLOBALS['TL_LANG']['tl_track']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => array('tl_track', 'toggleIcon'),
            ),
        ),
    ),
    // Palettes
    'palettes'    => array(
        '__selector__' => array(),
        'default'      => '{title_legend},title,author;{publish_legend},published',
    ),
    // Subpalettes
    'subpalettes' => array(),
    // Fields
    'fields'      => array(
        'id'        => array(
            'label'  => array('ID'),
            'search' => true,
            'sql'    => "int(10) unsigned NOT NULL auto_increment",
        ),
        'sorting'   => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'tstamp'    => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'title'     => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_track']['title'],
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'eval'      => array('mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'author'    => array(
            'label'      => &$GLOBALS['TL_LANG']['tl_track']['author'],
            'default'    => BackendUser::getInstance()->id,
            'exclude'    => true,
            'search'     => true,
            'inputType'  => 'select',
            'foreignKey' => 'tl_user.name',
            'eval'       => array('doNotCopy' => true, 'mandatory' => true, 'chosen' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'),
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => array('type' => 'hasOne', 'load' => 'eager'),
        ),
        'keywords'  => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_track']['keywords'],
            'exclude'   => true,
            'inputType' => 'textarea',
            'search'    => true,
            'eval'      => array('style' => 'height:60px', 'decodeEntities' => true),
            'sql'       => "text NULL",
        ),
        'published' => array(
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_track']['published'],
            'inputType' => 'checkbox',
            'eval'      => array('submitOnChange' => true, 'doNotCopy' => true),
            'sql'       => "char(1) NOT NULL default ''",
        ),
    ),
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_track extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }


    /**
     * Return the "toggle visibility" button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(Input::get('tid')))
        {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }


        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }


        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }


}
