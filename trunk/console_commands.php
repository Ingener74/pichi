<?php

require_once 'Console/CommandLine.php'; // Command Line options
$args = new Console_CommandLine(array(
    'description' => 'Pichi bot',
    'version'     => $config['pichi_version']
));

$args->addOption('database', array(
    'short_name'  => '-b',
    'long_name'   => '--db',
    'action'      => 'StoreString',
    'description' => 'Database name',
    'default'     => "{$config['db_file']}" // a default value
));


$args->addOption('user', array(
    'short_name'  => '-u',
    'long_name'   => '--user',
    'action'      => 'StoreInt',
    'description' => 'As userid of system'
));

$args->addOption('group', array(
    'short_name'  => '-g',
    'long_name'   => '--group',
    'action'      => 'StoreInt',
    'description' => 'As usergroup of system'
));

$args->addOption('database', array(
    'short_name'  => '-b',
    'long_name'   => '--db',
    'action'      => 'StoreString',
    'description' => 'Database name',
    'default'     => "{$config['db_file']}" // a default value
));


// add a global option to make the program verbose
$args->addOption('verbose', array(
    'short_name'  => '-v',
    'long_name'   => '--verbose',
    'action'      => 'StoreTrue',
    'description' => 'turn on verbose output (debug mode)'
));

try 
{
	$arg = $args->parse();
	$config['db_file'] = $arg->options['database'];
	if($arg->options['verbose'])
	{
		$config['debug'] = TRUE;
	}
	if($arg->options['daemon'] && RUN_OS == "Nix")
	{
		System_Daemon::setOption("appName", "pichi-bot");
		System_Daemon::setOption("appDir", dirname(__FILE__));
		System_Daemon::setOption("logLocation", dirname(__FILE__) . "/pichi.log");
		System_Daemon::setOption("appPidLocation", dirname(__FILE__) . "/" . System_Daemon::getOption("appName") . "/pichi.pid");
		if($arg->options['group'])
			System_Daemon::setOption("appRunAsGID", $arg->options['group']);
		if($arg->options['user'])
			System_Daemon::setOption("appRunAsGID", $arg->options['user']);
		System_Daemon::start();
		$config['daemon_mode'] = TRUE;
	}
	else
	{
		$config['daemon_mode'] = FALSE;
	}
} catch (Exception $exc) {
	$args->displayError($exc->getMessage());
}

// leeks
unset($args);
unset($arg);

?>
