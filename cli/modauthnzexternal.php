#!/usr/bin/env php
<?php
/**
 * This is a CRON script which should be called from the command-line,
 * not the web. For example something like:
 * env php /path/to/joomla/cli/app.php
 */

// Make sure we're being called from the command line, not a web interface
if (PHP_SAPI !== 'cli') die('This is a command line only application.');

// Set flag that this is a valid Joomla entry point
define('_JEXEC', 1);

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', 1);

// Load system defines
if (!defined('_JDEFINES')) {
	define('JPATH_BASE', dirname(dirname(__FILE__)));
	require_once JPATH_BASE . '/includes/defines.php';
}
require_once JPATH_BASE . '/includes/framework.php';

// Fool Joomla into thinking we're in the administrator with com_lawnvoice as active component
$app = JFactory::getApplication('site');
$_SERVER['HTTP_HOST'] = 'domain.com';
$_SERVER['REQUEST_METHOD'] = 'GET';

class CliModAuthnzExternal extends JApplicationCli {

	public function doExecute() {
								
		$this->addLogger();

		JLog::add('### Starting Auth Request ###', JLog::INFO, 'ModAuthnzExternal');

        $app = JFactory::getApplication();
		
		$stdin = fopen('php://stdin', 'r');
		stream_set_blocking($stdin, false);

        // Get the log in credentials.
		$credentials = [];
		$credentials['username']  = trim(fgets($stdin));
		$credentials['password']  = trim(fgets($stdin));
		$credentials['secretkey'] = null;
        
        $options = [];
		$options['remember'] = 0;
		$options['return']   = '';
		
		// foreach ($_ENV as $k => $v) {
		// 	JLog::add($k.'='.$v, JLog::INFO, 'ModAuthnzExternal');
		// }

		// Accept the login if the user name matchs the password
		if (true !== $app->login($credentials, $options)) {
			$msg = 'Login Failed for'.$credentials['username'];
			JLog::add($msg, JLog::NOTICE, 'ModAuthnzExternal');
			fwrite(STDERR, "$msg\n");
			exit(1);
		} else {
			$msg = 'Login Success for '.$credentials['username'];
			JLog::add($msg, JLog::NOTICE, 'ModAuthnzExternal');
			// fwrite(STDERR, "$msg\n");
			$user = JFactory::getUser();
			JLog::add(print_r($user, true), JLog::INFO, 'ModAuthnzExternal');
			// putenv('JOOMLA_ID', $user->id);
			// apache_setenv('JOOMLA_ID', $user->id);
			// fwrite(STDOUT, "JOOMLA_ID {$user->id}");
			// putenv('JOOMLA_USERNAME', $user->username);
			// apache_setenv('JOOMLA_USERNAME', $user->username);
			// fwrite(STDOUT, "JOOMLA_USERNAME {$user->username}");
			// putenv('JOOMLA_NAME', $user->name);
			// apache_setenv('JOOMLA_NAME', $user->name);
			// fwrite(STDOUT, "JOOMLA_NAME {$user->name}");
			// putenv('JOOMLA_EMAIL', $user->email);
			// apache_setenv('JOOMLA_EMAIL', $user->email);
			// fwrite(STDOUT, "JOOMLA_EMAIL {$user->email}");
			exit(0);
		}
	}

	protected function addLogger() {
		JLog::addLogger(
			array(
				 // Sets file name
				 'text_file' => 'ModAuthnzExternal.log.php'
			),
			// Sets messages of all log levels to be sent to the file
			JLog::ALL,
			// The log category/categories which should be recorded in this file
			// In this case, it's just the one category from our extension, still
			// we need to put it inside an array
			['ModAuthnzExternal']
		);
	}
}

JApplicationCli::getInstance('CliModAuthnzExternal')->execute();


// https://github.com/phokz/mod-auth-external/tree/master/mod_authnz_external
/***
#!/usr/bin/php
<?php

// Test authenticator using pipe method.  Logins will be accepted if the
// login and the password are identical, and will be rejected otherwise.
//
// This authenticator does copious logging by writing all sorts of stuff to
// STDERR.  A production authenticator would not normally do this, and it
// *especially* would not write the plain text password out to the log file.

// Get the name of this program
$prog = $argv[0];

// Get the user name
$user = trim(fgets(STDIN));

// Get the password
$pass = trim(fgets(STDIN));

// Print them to the error_log file
fwrite(STDERR, $prog . ": user='" . $user . "' pass='" . $pass . "'\n");

foreach ($_ENV as $k => $v)
{
	fwrite(STDERR, $prog . ': ' . $k . '=' . $v . "\n");
}

// Accept the login if the user name matchs the password
if ($user == $pass)
{
	fwrite(STDERR, $prog . ": login matches password - Accepted\n");
	exit(0);
}
else
{
	fwrite(STDERR, $prog . ": login doesn't match password - Rejected\n");
	exit(1);
}

?>
***/