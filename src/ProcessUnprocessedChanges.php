#!/usr/bin/php -f
<?php
/**
 * FlexiBee WebHook processor - Zpracování zatím nezachycených úprav
 *
 * @author     Vítězslav Dvořák <info@vitexsofware.cz>
 * @copyright  (G) 2018 Vitex Software
 */
define('EASE_APPNAME', 'ProcessUnprocessedChanges');

require_once '../vendor/autoload.php';
$shared = new Ease\Shared();
$shared->loadConfig('../client.json', true);

$hooker                   = new \FlexiPeeHP\Bricks\HookReciever();
$hooker->defaultUrlParams = ['limit' => 1000];
$hooker->debug            = true;

$globalChangeVersion = intval($hooker->getGlobalVersion());

$hooker->addStatusMessage(sprintf(_('Last processed change %d Last Availble Change: %d ( %d to process ) '),
        $hooker->lastProcessedVersion, $globalChangeVersion,
        $globalChangeVersion - $hooker->lastProcessedVersion), 'info');

$hooker->lock();
while ($globalChangeVersion > $hooker->lastProcessedVersion) {
    $hooker->getColumnsFromFlexibee('*',
        empty($hooker->lastProcessedVersion) ? [] :
            ['start' => $hooker->lastProcessedVersion + 1]);
    $hooker->takeChanges(json_decode($hooker->lastCurlResponse, TRUE));
    if (count($hooker->changes)) {
        $hooker->addStatusMessage(sprintf(_('%d unprocessed changes found'),
                count($hooker->changes)));
        $hooker->processChanges();
        $hooker->addStatusMessage(sprintf(_('Done for now. Last processed change %d Last Availble Change: %d ( %d to process )'),
                $hooker->lastProcessedVersion, $hooker->getGlobalVersion(),
                $globalChangeVersion - $hooker->lastProcessedVersion), 'success');
    } else {
        $hooker->addStatusMessage(_('No changes to process'));
        break;
    }
}
$hooker->unlock();
$hooker->addStatusMessage(_('done'));
