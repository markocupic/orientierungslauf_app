<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Table tl_race
 */
$GLOBALS['TL_DCA']['tl_race'] = array(

    // Config
    'config'      => array(
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'ptable'           => 'tl_track',
        'ctable'           => 'tl_run',
        'onload_callback'  => array(//array('tl_race', 'showJsLibraryHint')
        ),
        'sql'              => array(
            'keys' => array(
                'id'         => 'primary',
                'pid' => 'index',
            ),
        ),
    ),
    // List
    'list'        => array(
        'sorting'           => array(
            'mode'                  => 1,
            'fields'                => array('title'),
            //'flag'                    => 12,
            'headerFields'          => array('id', 'title', 'author'),
            'child_record_callback' => array('tl_race', 'childRecordCb'),
        ),
        'label'             => array(
            'fields' => array('title'),
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
            'editheader' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_race']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'header.gif',
                //'button_callback'     => array('tl_track', 'editHeader')
            ),
            'add_runners'       => array(
                'label' => &$GLOBALS['TL_LANG']['tl_race']['add_runners'],
                'href'  => 'table=tl_run',
                'icon'  => 'system/modules/mcupic_orientierungslauf_app/public/group_add.png',
            ),
            'copy'       => array(
                'label' => &$GLOBALS['TL_LANG']['tl_race']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ),
            'delete'     => array(
                'label'      => &$GLOBALS['TL_LANG']['tl_race']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ),
            'show'       => array(
                'label' => &$GLOBALS['TL_LANG']['tl_race']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ),
            'toggle'     => array(
                'label'           => &$GLOBALS['TL_LANG']['tl_race']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => array('tl_race', 'toggleIcon'),
            ),
        ),
    ),
    // Palettes
    'palettes'    => array(
        //'__selector__'                => array('type', 'addImage', 'sortable', 'useImage', 'protected'),
        'default' => '{type_legend},title;',
    ),
    // Subpalettes
    'subpalettes' => array(//
    ),
    // Fields
    'fields'      => array(
        'id'     => array(
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid'    => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'tstamp' => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),

        'title'  => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_race']['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => array('maxlength' => 255, 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
    ),
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_race extends Backend
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

        $href .= '&amp;id=' . Input::get('id') . '&amp;tid=' . $row['id'] . '&amp;state=' . $row['invisible'];

        if ($row['invisible'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['invisible'] ? 0 : 1) . '"') . '</a> ';
    }


    /**
     * Toggle the visibility of an element
     *
     * @param integer $intId
     * @param boolean $blnVisible
     * @param DataContainer $dc
     */
    public function toggleVisibility($intId, $blnVisible, DataContainer $dc = null)
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        $objVersions = new Versions('tl_race', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_race']['fields']['invisible']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_race']['fields']['invisible']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, ($dc ?: $this));
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, ($dc ?: $this));
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_race SET tstamp=" . time() . ", invisible='" . ($blnVisible ? '' : 1) . "' WHERE id=?")->execute($intId);

        $objVersions->create();
    }

    /**
     * child-record-callback
     *
     * @param array
     * @return string
     */
    public function childRecordCb($row)
    {

        return $row['title'];
    }

    /**
     * Return the cut-image-button
     *
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function buttonCbCutImage($row, $href, $label, $title, $icon, $attributes)
    {

        return '<a href="' . $this->addToUrl($href . '&id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }



    /**
     * Get all Courses
     * @return array
     */
    public function courseOptionsCallback()
    {
        $row = array();
        $objDatabase = \Database::getInstance()->prepare("SELECT * FROM tl_track WHERE published=?")->execute(1);
        while ($objDatabase->next())
        {
            $row[$objDatabase->id] = $objDatabase->title;
        }
        return $row;
    }
}
