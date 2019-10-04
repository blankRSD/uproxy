<?php
namespace Acme;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

class UnsetCommand extends Command
{
	public function configure()
	{
		$this->setName('unset')
			 ->setDescription('Unset the set system-wide proxy in your system');
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$this->removeNpmProxy($output)
			 ->removeGitProxy($output);
	}

	private function removeNpmProxy($output)
	{
		$command = "npm config rm proxy && npm config rm https-proxy"; 
		$process = new Process($command);
		$process->run();

		if(!$process->isSuccessful())
			throw new ProcessFailedException($process);
		else
			$output->writeln("<comment>NPM proxy unset!</comment>");

		return $this;
	}

	private function removeGitProxy($output)
	{
		$command = "git config --global --unset http.proxy && git config --global --unset https.proxy";
		$process = new Process($command);
		$process->run();

		if(!$process->isSuccessful())
			throw new ProcessFailedException($process);
		else
			$output->writeln("<comment>Git proxy unset!</comment>");

		return $this;
	}
	
}
