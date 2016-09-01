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
 * Table tl_run
 */
$GLOBALS['TL_DCA']['tl_run'] = array(

    // Config
    'config'      => array(
        'dataContainer'    => 'Table',
        'ptable'           => 'tl_race',
        'switchToEdit'     => true,
        'enableVersioning' => true,
        'onload_callback'  => array(//
        ),
        'sql'              => array(
            'keys' => array(
                'token' => 'unique',
                'id' => 'primary',
            ),
        ),
    ),
    // List
    'list'        => array(
        'sorting'           => array(
            'mode'                  => 4,
            'fields'                => array('name'),
            'flag'                    => 12,
            'headerFields'          => array('id', 'title'),
            'child_record_callback' => array('tl_run', 'childRecordCb'),
        ),
        'label'             => array(
            'fields' => array('name'),
            'format' => '%s',
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

            'edit' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_run']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
                //'button_callback'     => array('tl_run', 'editHeader')
            ),

            'delete'     => array(
                'label'      => &$GLOBALS['TL_LANG']['tl_run']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ),
            'show'       => array(
                'label' => &$GLOBALS['TL_LANG']['tl_run']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ),
            'toggle'     => array(
                'label'           => &$GLOBALS['TL_LANG']['tl_run']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => array('tl_run', 'toggleIcon'),
            ),
        ),
    ),
    // Palettes
    'palettes'    => array(
        '__selector__' => array(),
        'default'      => '{title_legend},name,token,startTime,endTime,runningTime,finished,currentPositionX,currentPositionY;',
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
        'pid'        => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'tstamp'    => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'token'  => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_race']['token'],
            'exclude'   => true,
            'default'   => rand(10000000, 99999999),
            'search'    => true,
            'inputType' => 'text',
            'eval'      => array('mandatory' => true, 'doNotCopy' => true, 'unique' => true, 'minlength' => 8, 'maxlength' => 8, 'minval' => '10000000', 'maxval' => 99999999),
            'sql'       => "int(10) unsigned NOT NULL default '0'",
        ),

        'endTime'    => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_run']['endTime'],
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'eval'      => array(),
            'sql' => "int(10) unsigned NOT NULL default '0'",        ),
        'startTime'    => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_run']['startTime'],
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'eval'      => array(),
            'sql' => "int(10) unsigned NOT NULL default '0'",        ),
        'runningTime'    => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_run']['runningTime'],
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'eval'      => array(),
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'name'     => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_run']['name'],
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'eval'      => array('mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'runInfo'     => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_run']['runInfo'],
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'sql'                     => "blob NULL"
        ),
        'finished'     => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_run']['finished'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'search'    => true,
            'sql'       => "char(1) NOT NULL default ''",
        ),
        'currentPositionX'     => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_run']['currentPositionX'],
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'eval'      => array('mandatory' => false, 'decodeEntities' => true, 'maxlength' => 7),
            'sql'       => "varchar(7) NOT NULL default ''",
        ),
        'currentPositionY'     => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_run']['currentPositionY'],
            'exclude'   => true,
            'inputType' => 'text',
            'search'    => true,
            'eval'      => array('mandatory' => false, 'decodeEntities' => true, 'maxlength' => 7),
            'sql'       => "varchar(7) NOT NULL default ''",
        )

    )
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_run extends Backend
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

    /**
     * child-record-callback
     *
     * @param array
     * @return string
     */
    public function childRecordCb($row)
    {
        return $row['name'] . ' token: ' . $row['token'];
    }

}
