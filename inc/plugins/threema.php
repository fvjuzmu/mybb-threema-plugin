<?php
/**
 * MyBB 1.8
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/about/license
 *
 */

// Make sure we can't access this file directly from the browser.
if(!defined('IN_MYBB'))
{
    die('This file cannot be accessed directly.');
}

require_once 'vendor/autoload.php';
use Threema\MsgApi\Connection;
use Threema\MsgApi\ConnectionSettings;
use Threema\MsgApi\Receiver;


$plugins->add_hook('newthread_do_newthread_end', 'threema_send_new_thread_notification');
$plugins->add_hook('datahandler_post_insert_post_end', 'threema_send_new_reply_notification');

function threema_info()
{
    return array(
        'name' => 'Threema Notifications',
        'description' => 'Sends informations about new posts to users with the help of Threema',
        'website' => 'https://github.com/fvjuzmu/mybb-threema-plugin',
        'author' => 'soulflyman',
        'authorsite' => 'https://github.com/soulflyman',
        'version' => '1.0',
        'compatibility' => '18*',
        'codename' => 'threema'
    );
}

function threema_send_new_thread_notification()
{
    try
    {
        if(threema_is_forum_blacklisted())
        {
            return;
        }

        $userName = $GLOBALS['new_thread']['username'];
        $parentID = $GLOBALS['forum']['pid'];
        $parentName = $GLOBALS['forum_cache'][$parentID]['name'];
        $threadID = $GLOBALS['thread_info']['tid'];
        $postSubject = $GLOBALS['new_thread']['subject'];

        $msgUrl = $GLOBALS['settings']['bburl'] . '/showthread.php?tid=' .$threadID . '&action=newpost';
        $msg = "*" . $postSubject . "* \n\n" . $userName . " hat dieses neue Thema in _" . $parentName . "_ erstellt. \n\n" . $msgUrl;

        threema_send_notifications($msg);
    }
    catch(Exception $e)
    {
    }
}

function threema_send_new_reply_notification()
{
    try
    {
        if($GLOBALS['post']['savedraft'] == 1 || threema_is_forum_blacklisted())
        {
            return;
        }

        $userName = $GLOBALS['post']['username'];
        $forumName = $GLOBALS['forum']['name'];
        $parentID = $GLOBALS['forum']['pid'];
        $parentName = $GLOBALS['forum_cache'][$parentID]['name'];
        $threadID = $GLOBALS['post']['tid'];
        $postSubject = $GLOBALS['post']['subject'];

        $msgUrl = $GLOBALS['settings']['bburl'] . '/showthread.php?tid=' .$threadID . '&action=newpost';
        $msg = "*" . $postSubject . "* \n\n" . $userName . " hat auf ein Thema in _" . $parentName . "->" . $forumName . "_ geantwortet. \n\n" . $msgUrl;

        threema_send_notifications($msg);
    }
    catch(Exception $e)
    {
    }
}

function threema_is_forum_blacklisted()
{
    $blacklist = explode(',', $GLOBALS['settings']['threema_blacklist']);

    if(!in_array($GLOBALS['forum']['pid'], $blacklist) && !in_array($GLOBALS['forum']['fid'], $blacklist))
    {
        return false;
    }

    return true;
}

function threema_send_notifications($message)
{
    global $db;

    $fid = "fid" . $GLOBALS['settings']['threema_fid'];

    $threema = threemaConnect();

    // GET Threema IDs of alle users that are not banned

    $queryUserKeys = $db->simple_select("userfields", "ufid, " . $fid, $fid . " is not NULL and " . $fid . " != '' and ufid not in (select uid from " . $db->table_prefix . "banned where lifted = 0)", array(
        "order_by" => 'ufid',
        "order_dir" => 'DESC'
    ));

    //send notification to every user except the user which posted
    while($userKey = $db->fetch_array($queryUserKeys))
    {
        if($userKey['ufid'] == $GLOBALS['uid'])
        {
            continue;
        }

        $receiver = new Receiver($userKey[$fid], Receiver::TYPE_ID);
        $result = $threema->sendSimple($receiver, $message);
        if(!$result->isSuccess())
        {
            //    echo 'Error: '.$result->getErrorMessage();
        }
    }
}

function threemaConnect()
{
    global $mybb;

    $settings = new ConnectionSettings(
        $mybb->settings['threema_id'],
        $mybb->settings['threema_secret']
    );

    $publicKeyStore = new Threema\MsgApi\PublicKeyStores\File(__DIR__ . '/vendor/threema_keystore.txt');

    return new Connection($settings, $publicKeyStore);//*/
}

/*
 * _install():
 *   Called whenever a plugin is installed by clicking the 'Install' button in the plugin manager.
 *   If no install routine exists, the install button is not shown and it assumed any work will be
 *   performed in the _activate() routine.
*/
function threema_install()
{
    global $db;

    $setting_group = array(
        'name' => 'threema_settings',
        'title' => 'Threema settings',
        'description' => 'Configure Threema Notifications',
        'disporder' => 5, // The order your setting group will display
        'isdefault' => 0
    );

    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(
        'threema_id' => array(
            'title' => 'Threema-ID',
            'description' => 'Your Threema Gateway ID',
            'optionscode' => 'text',
            'disporder' => 1
        ),
        'threema_secret' => array(
            'title' => 'Secret',
            'description' => 'Your Threema Gateway Secret',
            'optionscode' => 'text',
            'disporder' => 2
        ),
        'threema_fid' => array(
            'title' => 'Custom Profile Field ID',
            'description' => 'The ID of the "Custom Profile Field" in which the users can enter there Threema-ID',
            'optionscode' => 'text',
            'disporder' => 3
        ),
        'threema_blacklist' => array(
            'title' => 'Forum Blacklist',
            'description' => 'A Blacklist of Forum-IDs seperated by comma. All listet forums do not send a notification.',
            'optionscode' => 'text',
            'disporder' => 4
        )
    );

    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid'] = $gid;
        $db->insert_query('settings', $setting);
    }

    // Don't forget this!
    rebuild_settings();
}

/*
 * _is_installed():
 *   Called on the plugin management page to establish if a plugin is already installed or not.
 *   This should return TRUE if the plugin is installed (by checking tables, fields etc) or FALSE
 *   if the plugin is not installed.
*/
function threema_is_installed()
{
    global $mybb;

    if( array_key_exists('threema_id', $mybb->settings) &&
        array_key_exists('threema_secret', $mybb->settings) &&
        array_key_exists('threema_blacklist', $mybb->settings) &&
        array_key_exists('threema_fid', $mybb->settings) )
    {
        return true;
    }
    else
    {
        return false;
    }
}

/*
 * _uninstall():
 *    Called whenever a plugin is to be uninstalled. This should remove ALL traces of the plugin
 *    from the installation (tables etc). If it does not exist, uninstall button is not shown.
*/
function threema_uninstall()
{
    global $db;

    $db->delete_query('settings', "name IN ('threema_id','threema_secret','threema_blacklist','threema_fid')");
    $db->delete_query('settinggroups', "name = 'threema_settings'");

    // Don't forget this
    rebuild_settings();
}
