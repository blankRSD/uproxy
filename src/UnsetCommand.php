<?php
namespace Acme;

use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class UnsetCommand extends Command
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
		$this->setName('unset')
			 ->setDescription('Unset the set system-wide proxy in your system');
	}

	/**
	 * Undocumented function
	 *
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return void
	 */
	public function execute(InputInterface $input, OutputInterface $output)
	{
		$this->removeNpmProxy($output)
			 ->removeGitProxy($output)
			 ->removeAptProxy($output)
			 ->removeEnvProxy($output);
	}

	/**
	 * Remove proxy of NPM
	 *
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return $this
	 */
	private function removeNpmProxy(OutputInterface $output)
	{
		$commands = [
			"npm config rm proxy",
			"npm config rm https-proxy"
		];
		$process = new Process(implode(' && ', $commands));
		$process->run();

		if(!$process->isSuccessful())
			throw new ProcessFailedException($process);
		else
			$output->writeln("<comment>NPM proxy unset!</comment>");

		return $this;
	}

	/**
	 * Remove proxy for Git
	 *
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return $this
	 */
	private function removeGitProxy(OutputInterface $output)
	{
		$commands = [
			"git config --global --unset http.proxy",
			"git config --global --unset https.proxy"
		];
		$process = new Process(implode(' && ', $commands));
		$process->run();

		if(!$process->isSuccessful())
			throw new ProcessFailedException($process);
		else
			$output->writeln("<comment>Git proxy unset!</comment>");

		return $this;
	}

	/**
	 * Remove proxy for Apt
	 *
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return $this
	 */
	private function removeAptProxy(OutputInterface $output)
	{
		if (!$this->filesystem->exists('/etc/apt/apt.conf'))
			return $this;

		$this->filesystem->copy('/etc/apt/apt.conf.bak', '/etc/apt/apt.conf', true);
		$output->writeln("<comment>Apt proxy unset!</comment>");

		return $this;
	}

	/**
	 * Remove Environment proxy
	 *
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return $this
	 */
	private function removeEnvProxy(OutputInterface $output)
	{
		if (!$this->filesystem->exists('/etc/environment'))
			return $this;

		$this->filesystem->copy('/etc/environment.bak', '/etc/environment', true);
		$output->writeln("<comment>Env proxy unset!</comment>");

		return $this;
	}
	
}
