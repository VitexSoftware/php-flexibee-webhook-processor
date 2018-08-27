<?php

/**
 * clientzone - WebHook Acceptor.
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2017 VitexSoftware v.s.cz
 */
require_once '../vendor/autoload.php';
$shared = new Ease\Shared();
$shared->loadConfig('../client.json',true);

$hooker = new \FlexiPeeHP\Bricks\HookReciever();
$hooker->takeChanges($hooker->listen());
$hooker->processChanges();
