<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Table tl_position
 */
$GLOBALS['TL_DCA']['tl_position'] = array(

    // Config
    'config'      => array(
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'ptable'           => 'tl_track',
        'onload_callback'  => array(//array('tl_position', 'showJsLibraryHint')
        ),
        'sql'              => array(
            'keys' => array(
                'id'                    => 'primary',
                'pid,invisible,sorting' => 'index',
            ),
        ),
    ),
    // List
    'list'        => array(
        'sorting'           => array(
            'mode'                  => 4,
            'fields'                => array('sorting'),
            'flag'                    => 12,
            'headerFields'          => array('id', 'title', 'author'),
            'child_record_callback' => array('tl_position', 'childRecordCb'),
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
            'edit'   => array(
                'label' => &$GLOBALS['TL_LANG']['tl_position']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ),
            'copy'   => array(
                'label' => &$GLOBALS['TL_LANG']['tl_position']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ),
            'cut'    => array(
                'label'           => &$GLOBALS['TL_LANG']['tl_position']['cut'],
                'href'            => 'act=paste&amp;mode=cut',
                'icon'            => 'cut.gif',
                'button_callback' => array('tl_position', 'buttonCbCutImage'),
            ),
            'delete' => array(
                'label'      => &$GLOBALS['TL_LANG']['tl_position']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ),
            'show'   => array(
                'label' => &$GLOBALS['TL_LANG']['tl_position']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ),
        ),
    ),
    // Palettes
    'palettes'    => array(
        //'__selector__'                => array('type', 'addImage', 'sortable', 'useImage', 'protected'),
        'default' => '{type_legend},title;{coordinates_legend},longitude,latitude;{invisible_legend:hide},invisible',
    ),
    // Subpalettes
    'subpalettes' => array(//
    ),
    // Fields
    'fields'      => array(
        'id'        => array(
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ),
        'pid'       => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'sorting'   => array(
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp'    => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'text'      => array(
            'label'       => &$GLOBALS['TL_LANG']['tl_position']['text'],
            'exclude'     => true,
            'search'      => true,
            'inputType'   => 'textarea',
            'eval'        => array('mandatory' => true, 'rte' => 'tinyMCE', 'helpwizard' => true),
            'explanation' => 'insertTags',
            'sql'         => "mediumtext NULL",
        ),
        'title'     => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_position']['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => array('maxlength' => 255, 'tl_class' => 'w50'),
            'sql'       => "varchar(255) NOT NULL default ''",
        ),
        'latitude'  => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_position']['latitude'],
            'exclude'   => true,
            'default'   => 1200000,
            'inputType' => 'text',
            'eval'      => array('rgxp' => 'natural', 'minval' => 1073000, 'maxval' => 1300000,'tl_class' => 'w50'),
            'sql'       => "int(7) unsigned NOT NULL default '0'",
        ),
        'longitude' => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_position']['longitude'],
            'exclude'   => true,
            'default'   => 2600000,
            'inputType' => 'text',
            'eval'      => array('rgxp' => 'natural', 'minval' => 2480000, 'maxval' => 2837000,'tl_class' => 'w50'),
            'sql'       => "int(7) unsigned NOT NULL default '0'",
        ),
        'invisible' => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_position']['invisible'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'sql'       => "char(1) NOT NULL default ''",
        ),
    ),
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_position extends Backend
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

        $objVersions = new Versions('tl_position', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_position']['fields']['invisible']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_position']['fields']['invisible']['save_callback'] as $callback)
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
        $this->Database->prepare("UPDATE tl_position SET tstamp=" . time() . ", invisible='" . ($blnVisible ? '' : 1) . "' WHERE id=?")->execute($intId);

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
}
