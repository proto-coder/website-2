<?php
use Destiny\UserRole;
use Destiny\AppEvent;
use Destiny\Service\UserService;
use Destiny\Service\AuthenticationService;
use Destiny\Application;
use Destiny\AppException;
use Destiny\Utils\Http;
use Destiny\SessionCredentials;
use Destiny\SessionCookie;
use Destiny\SessionInstance;
use Destiny\Session;
use Destiny\Config;
use Destiny\Service\ChatIntegrationService;

ini_set ( 'max_execution_time', 30 );
ini_set ( 'mysql.connect_timeout', 10 );
ini_set ( 'session.gc_maxlifetime', 5 * 60 * 60 );

$context = new stdClass ();
$context->log = 'http';
require __DIR__ . '/../lib/boot.php';
$app = Application::instance ();

// Setup user session
$app->setSession ( new SessionInstance () );
$session = $app->getSession ();
$session->setSessionCookie ( new SessionCookie ( Config::$a ['cookie'] ) );
$session->setCredentials ( new SessionCredentials () );

// Puts the session into the cache
$session->addCredentialHandler ( function (SessionInstance $session, SessionCredentials $credentials) {
	ChatIntegrationService::instance ()->updateSession ( $session, $credentials );
} );
// Removes session from cache
$session->addCleanupHandler ( function (SessionInstance $session) {
	ChatIntegrationService::instance ()->deleteSession ( $session );
} );

// Start the session if a valid session cookie is found
Session::start ( Session::START_IFCOOKIE );

$userManager = UserService::instance ();
$authService = AuthenticationService::instance ();

// Check if the users session has been flagged for update
if (Session::isStarted () && Session::getCredentials ()->isValid ()) {
	$cache = $app->getCacheDriver ();
	$refreshId = sprintf ( 'refreshsession-%s', Session::getSessionId () );
	if ($cache->fetch ( $refreshId ) === 1) {
		$cache->delete ( $refreshId );
		$user = $userManager->getUserById ( Session::getCredentials ()->getUserId () );
		$authService->login ( $user, 'refreshed' );
		$app->addEvent ( new AppEvent ( array (
			'type' => AppEvent::EVENT_INFO,
			'label' => 'Your session has been updated',
			'message' => sprintf ( 'Nothing to worry about, just letting you know...', Session::getCredentials ()->getUsername () ) 
		) ) );
	}
}

// If the session hasnt started, or the data is not valid (result from php clearing the session data), check the Remember me cookie
if (! Session::isStarted () || ! Session::getCredentials ()->isValid ()) {
	$userId = $authService->getRememberMe ();
	if ($userId !== false) {
		$user = $userManager->getUserById ( $userId );
		if (! empty ( $user )) {
			$authService->login ( $user, 'rememberme' );
			$authService->setRememberMe ( $user );
			$app->addEvent ( new AppEvent ( array (
				'type' => AppEvent::EVENT_DANGER,
				'label' => 'You have been automatically logged in',
				'message' => sprintf ( 'Please logout if you are not "%s"', Session::getCredentials ()->getUsername () ) 
			) ) );
		}
	}
}

// Attempts to find a route and execute the action
$app->executeRequest ( (isset ( $_SERVER ['REQUEST_URI'] )) ? $_SERVER ['REQUEST_URI'] : '', $_SERVER ['REQUEST_METHOD'] );
?>