<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Markocupic;


/**
 * Front end Custom Footer.
 *
 * @author Marko Cupic <m.cupic@gmx.ch>
 */
class OrientierungslaufApp extends \Module
{

    /**
     * template
     * @var string
     */
    protected $strTemplate = 'mod_orientierungslauf_app';

    /**
     * @var
     */
    protected $session;


    /**
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objRun = $this->Database->prepare("UPDATE tl_run SET runInfo='' WHERE id=?")->execute(7);
            // $objRun = $this->Database->prepare("UPDATE tl_run SET starttime=? WHERE id=?")->execute(0,7);
            $objRun = $this->Database->prepare("UPDATE tl_run SET endtime=? WHERE id=?")->execute(0, 7);
            $objRun = $this->Database->prepare("UPDATE tl_run SET runningTime=? WHERE id=?")->execute(0, 7);
            $objRun = $this->Database->prepare("UPDATE tl_run SET finished='' WHERE id=?")->execute(7);


            /** @var \BackendTemplate|object $objTemplate */
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['custom_header'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }


        // Logout
        if (\Input::get('logout') == 'true')
        {
            unset($_SESSION['ORIENTIERUNGSLAUF_APP']);
            $this->redirect(str_replace('?logout=true', '', \Environment::get('request')));
        }

        // Set Session Array
        $_SESSION['ORIENTIERUNGSLAUF_APP'] = isset($_SESSION['ORIENTIERUNGSLAUF_APP']) ? $_SESSION['ORIENTIERUNGSLAUF_APP'] : array();


        if (!$this->getSession('loggedIn'))
        {

            $this->strTemplate = 'mod_orientierungslauf_app_loginform';

            if (\Input::post('FORM_SUBMIT') == 'tl_orientierungslauf_app' && \Input::post('token') != '')
            {

                $objRun = $this->Database->prepare('SELECT * FROM tl_run WHERE token=?')->limit(1)->execute(\Input::post('token'));
                if ($objRun->numRows)
                {
                    // If run has already ended
                    if ($objRun->finished)
                    {
                        $this->setSession('finishedRun', 1);
                        //$this->reload();
                    }

                    $this->setSession('token', \Input::post('token'));
                    $this->setSession('loggedIn', 1);
                    $this->setSession('runId', $objRun->id);


                    $objRace = $this->Database->prepare('SELECT * FROM tl_race WHERE id=?')->limit(1)->execute($objRun->pid);
                    if (!$objRace->numRows)
                    {
                        unset($_SESSION['ORIENTIERUNGSLAUF_APP']);
                        die('error ' . __METHOD__ . __LINE__);
                    }
                    $this->setSession('raceId', $objRace->id);

                    $objCourse = $this->Database->prepare('SELECT * FROM tl_track WHERE id=?')->limit(1)->execute($objRace->pid);
                    if (!$objCourse->numRows)
                    {
                        unset($_SESSION['ORIENTIERUNGSLAUF_APP']);
                        die('error ' . __METHOD__ . __LINE__);
                    }
                    $this->setSession('courseId', $objCourse->id);

                    if ($objRun->startTime < 1)
                    {
                        // Set the runInfo array and the start time
                        $objPosition = $this->Database->prepare('SELECT * FROM tl_position WHERE pid=? AND invisible=? ORDER BY sorting ASC')->execute($objCourse->id, '');
                        $runInfo = array();
                        while ($objPosition->next())
                        {
                            $runInfo[$objPosition->id] = array('passed' => 0, 'logTime' => 0, 'posX' => 0, 'posY' => 0);
                        }
                        $this->Database->prepare('UPDATE tl_run SET runInfo=? WHERE id=?')->execute(serialize($runInfo), $objRun->id);
                        $this->Database->prepare('UPDATE tl_run SET startTime=? WHERE id=?')->execute(time(), $objRun->id);
                    }

                }
                $this->reload();
            }

        }
        else
        {
            //check if item exists
            $objRun = $this->Database->prepare('SELECT * FROM tl_run WHERE id=?')->execute($this->getSession('runId'));
            if (!$objRun->numRows)
            {
                unset($_SESSION['ORIENTIERUNGSLAUF_APP']);
                $this->reload();
            }
            // Ajax handling
            if (\Environment::get('isAjaxRequest'))
            {
                $this->ajaxHandler();
            }
        }


        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {

        if (!$this->getSession('loggedIn'))
        {
            $this->Template->url = \Environment::get('request');
        }
        else
        {


            $courseId = $this->getSession('courseId');
            $runId = $this->getSession('runId');


            $objRun = $this->Database->prepare('SELECT * FROM tl_run WHERE id=?')->execute($runId);
            $runInfo = deserialize($objRun->runInfo, true);

            $objCourse = $this->Database->prepare('SELECT * FROM tl_track WHERE id=?')->limit(1)->execute($this->getSession('courseId'));
            $rows = array();
            $current = false;
            $objPosition = $this->Database->prepare('SELECT * FROM tl_position WHERE pid=? AND invisible=? ORDER BY sorting ASC')->execute($courseId, '');
            while ($objPosition->next())
            {
                $row = array();
                $row['class'] = '';
                $row['current'] = false;
                $row['logTime'] = ($runInfo[$objPosition->id]['logTime'] > 0) ? $runInfo[$objPosition->id]['logTime'] : '0';
                $row['logPosX'] = ($runInfo[$objPosition->id]['posX'] > 0) ? $runInfo[$objPosition->id]['posX'] : '0';
                $row['logPosY'] = ($runInfo[$objPosition->id]['posY'] > 0) ? $runInfo[$objPosition->id]['posY'] : '0';
                $row['passed'] = ($runInfo[$objPosition->id]['logTime'] > 0) ? 'true' : 'false';
                $row['savedToServer'] = ($runInfo[$objPosition->id]['logTime'] > 0) ? 'true' : 'false';

                if ($runInfo[$objPosition->id]['passed'])
                {
                    $row['class'] .= ' passed';
                }
                else
                {
                    if (!$current)
                    {
                        $current = $objPosition->id;
                        $row['current'] = true;
                        $this->Template->current = $current;
                        $row['class'] .= ' current';
                    }
                }
                $rows[] = array_merge($objPosition->row(), $row);
            }

            $this->Template->countItems = count($rows);

            $this->Template->positions = $rows;
            $this->Template->token = $objRun->token;
            $this->Template->trackName = $objCourse->title;
            $this->Template->athleteName = $objRun->name;
            $this->Template->startTime = $objRun->startTime;

            $this->Template->runInfo = $objRun->runInfo;
            $this->Template->logoutUrl = \Environment::get('request') . '?logout=true';
            $this->Template->finished = $objRun->finished == 1 ? true : false;
            $this->Template->runningTime = $objRun->runningTime > 0 ? round($objRun->runningTime / 60, 2) : 0;


        }


    }

    /**
     * Handling Ajax Requests
     */
    protected function ajaxHandler()
    {
        // Athlete has passed a position on the track
        if (\Input::get('token') && \Input::get('posId') && \Input::get('passed') == 'true')
        {
            $objRun = $this->Database->prepare('SELECT * FROM tl_run WHERE token=?')->execute(\Input::get('token'));
            if ($objRun->numRows)
            {
                $runInfo = deserialize($objRun->runInfo, true);
                $runInfo[\Input::get('posId')]['passed'] = 1;
                $runInfo[\Input::get('posId')]['posX'] = \Input::get('posX');
                $runInfo[\Input::get('posId')]['posY'] = \Input::get('posY');
                $runInfo[\Input::get('posId')]['logTime'] = (\Input::get('logTime') > 0) ? \Input::get('logTime') : time();


                $set = array(
                    'runInfo'         => serialize($runInfo),
                    'currentPositionX' => \Input::get('posX'),
                    'currentPositionY' => \Input::get('posY'),
                );
                $this->Database->prepare('UPDATE tl_run %s WHERE token=?')->set($set)->execute(\Input::get('token'));
                echo json_encode(array('success' => 'true'));
                exit();
            }
        }

        // Athlete has finished the race
        if (\Input::get('token') && \Input::get('finished') == 'true')
        {
            $objRun = $this->Database->prepare('SELECT * FROM tl_run WHERE token=?')->execute(\Input::get('token'));
            if ($objRun->numRows)
            {
                $endTime = (\Input::get('endTime') > 0 ) ? \Input::get('endTime') : time();
                $runningTime = $endTime - $objRun->startTime;
                $set = array(
                    'finished'    => 1,
                    'endTime'     => $endTime,
                    'runningTime' => $runningTime,
                );
                $this->Database->prepare('UPDATE tl_run %s WHERE token=?')->set($set)->execute(\Input::get('token'));
                echo json_encode(array('success' => 'true', 'runningTime' => $runningTime));
                exit();
            }
        }

        // Default
        echo json_encode(array('error' => 'true'));
        exit();
    }

    /**
     * @param $key
     * @param $value
     */
    protected function setSession($key, $value)
    {
        $_SESSION['ORIENTIERUNGSLAUF_APP'][$key] = $value;
        $this->session = $_SESSION['ORIENTIERUNGSLAUF_APP'];
    }

    /**
     * @param $key
     * @return null
     */
    protected function getSession($key)
    {
        if (isset($_SESSION['ORIENTIERUNGSLAUF_APP'][$key]))
        {
            return $_SESSION['ORIENTIERUNGSLAUF_APP'][$key];
        }
        else
        {
            return null;
        }
    }

    /**
     * Get distance in meters
     */
    protected function calculateDistance($posX, $posY, $targetX, $targetY)
    {
        $dX = abs($posX - $targetX);
        $dY = abs($posY - $targetY);
        return floor(($dX ^ 2 + $dY ^ 2) ^ 0.5);
    }

}
