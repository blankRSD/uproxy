<?php
namespace Acme;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SetCommand extends Command
{
	public function configure()
	{
		$this->setName('set')
			 ->setDescription('Set system-wide proxy in your system')
		   	 ->addArgument('host', InputArgument::REQUIRED, 'Hostname or Host-ip')
		   	 ->addArgument('port', InputArgument::REQUIRED, 'Port');
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$host = $input->getArgument('host');
		$port = $input->getArgument('port');

		$this->addNpmProxy($host, $port, $output)
			 ->addGitProxy($host, $port, $output);
	}

	private function addNpmProxy($host, $port, $output)
	{
		$command = "npm config set proxy http://{$host}:{$port} && npm config set https-proxy http://{$host}:{$port}";
		$process = new Process($command);
		$process->run();

		if(!$process->isSuccessful())
			throw new ProcessFailedException($process);
		else
			$output->writeln("<info>NPM proxy set</info>");

		return $this;
	}

	private function addGitProxy($host, $port, $output)
	{
		$command = "git config --global http.proxy http://{$host}:{$port} && git config --global https.proxy http://{$host}:{$port}";
		$process = new Process($command);
		$process->run();

		if(!$process->isSuccessful())
			throw new ProcessFailedException($process);
		else
			$output->writeln("<info>Git proxy set</info>");

		return $this;
	}
	
}
