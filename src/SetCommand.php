<?php
namespace Acme;

use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SetCommand extends Command
{
	private $filesystem;

	/**
	 * Constructor
	 *
	 * @param \Symfony\Component\Filesystem\Filesystem $filesystem
	 */
	public function __construct(Filesystem $filesystem)
	{
		$this->filesystem = $filesystem;
		parent::__construct();
	}
	/**
	 * Configure the command options
	 *
	 * @return void
	 */
	public function configure()
	{
		$this->setName('set')
			 ->setDescription('Set system-wide proxy in your system')
		   	 ->addArgument('host', InputArgument::REQUIRED, 'Hostname or Host-ip')
		   	 ->addArgument('port', InputArgument::REQUIRED, 'Port');
	}

	/**
	 * Execute the command
	 *
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return void
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		$host = $input->getArgument('host');
		$port = $input->getArgument('port');

		$this->addNpmProxy($host, $port, $output)
			 ->addGitProxy($host, $port, $output)
			 ->addAptProxy($host, $port, $output)
			 ->addEnvProxy($host, $port, $output);
	}

	/**
	 * Adds proxy for NPM
	 *
	 * @param string $host
	 * @param string $port
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return $this
	 */
	private function addNpmProxy($host, $port, OutputInterface $output)
	{
		$commands = [
			"npm config set proxy http://{$host}:{$port}",
			"npm config set https-proxy http://{$host}:{$port}"
		];
		$process = new Process(implode(' && ', $commands));
		$process->run();

		if(!$process->isSuccessful())
			throw new ProcessFailedException($process);
		else
			$output->writeln("<info>NPM proxy set</info>");

		return $this;
	}

	/**
	 * Adds proxy for Git
	 *
	 * @param string $host
	 * @param string $port
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return $this
	 */
	private function addGitProxy($host, $port, OutputInterface $output)
	{
		$commands = [
			"git config --global http.proxy http://{$host}:{$port}",
			"git config --global https.proxy http://{$host}:{$port}"
		];
		$process = new Process(implode(' && ', $commands));
		$process->run();

		if(!$process->isSuccessful())
			throw new ProcessFailedException($process);
		else
			$output->writeln("<info>Git proxy set</info>");

		return $this;
	}

	/**
	 * Adds proxy for Apt
	 *
	 * @param string $host
	 * @param string $port
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return $this
	 */
	private function addAptProxy($host, $port, OutputInterface $output)
	{
		if(!$this->filesystem->exists('/etc/apt/apt.conf'))
			$this->filesystem->touch('/etc/apt/apt.conf');

		$this->filesystem->copy('/etc/apt/apt.conf', '/etc/apt/apt.conf.bak', true);
		
		$lines = [
			"Acquire::ftp::proxy \"http://{$host}:{$port}\";",
			"Acquire::http::proxy \"http://{$host}:{$port}\";",
			"Acquire::https::proxy \"http://{$host}:{$port}\";",
		];

		$this->filesystem->appendToFile('/etc/apt/apt.conf', implode("\n", $lines));
		$output->writeln("<info>Apt proxy set</info>");

		return $this;
	}

	/**
	 * Add Environment proxy
	 *
	 * @param string $host
	 * @param string $port
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return $this
	 */
	private function addEnvProxy($host, $port, OutputInterface $output)
	{
		if (!$this->filesystem->exists('/etc/environment'))
			$this->filesystem->touch('/etc/environment');

		$this->filesystem->copy('/etc/environment', '/etc/environment.bak', true);

		$lines = [
			"http_proxy=\"http://{$host}:{$port}\"",
			"https_proxy=\"http://{$host}:{$port}\"",
			"ftp_proxy=\"http://{$host}:{$port}\"",
			"no_proxy=\"localhost,127.0.0.1,*.test,::1\""
		];

		$this->filesystem->appendToFile('/etc/environment', implode("\n", $lines));
		$output->writeln("<info>Env proxy set</info>");

		return $this;
	}

}
