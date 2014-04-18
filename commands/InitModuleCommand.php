<?php

namespace Bwlab\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Twig_Loader_String;
use Twig_Environment;
use Twig_Loader_Filesystem;

class InitModuleCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('module:init')
            ->setDescription('Module initialization')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //twig init
        $loader = new Twig_Loader_Filesystem(__DIR__.'/templates/module');
        $twig = new Twig_Environment($loader, array());

        $fs = new Filesystem();

        //setup dialog
        $dialog = $this->getHelper('dialog');

        //ask module name
        $name = $dialog->ask($output, '<comment>Module name to create</comment>: ');

        //name displaied
        $display_name = $dialog->ask($output, '<comment>Display module name</comment>: ');
        
        //module description
        $description = $dialog->ask($output, '<comment>Description</comment>: ');

        //author module
        $author = $dialog->ask($output, '<comment>Author</comment>: ');

        //category module
        $tabs = array(
            'others'=>'Others'
            );
        $idxtab = $dialog->select(
                    $output, 
                    '<comment>Category</comment>: ',
                    $tabs
                );

        $tab = $tabs[$idxtab];

        $output->writeln("Create module: ".$name);

        //config.xml
        try {
            $dir = _PS_MODULE_DIR_ .$name;
            $output->writeln('..create module dir: '.$dir);
            $fs->mkdir($dir);

            $output->writeln('..create main file : '.$name.'.php');
            $filename = $dir.DIRECTORY_SEPARATOR.$name.'.php';
            $fs->touch($filename);
            file_put_contents($filename,
                $twig->render(
                'main.php.twig',
                array(
                    'name' =>$name,
                    'display_name' =>$display_name,
                    'description' =>$description,
                    'author'=>$author,
                    'tab'=>$tab
                )
            )
                );

            $output->writeln('..create config.xml file');
            $filename = $dir.DIRECTORY_SEPARATOR.'config.xml';
            $fs->touch($filename);
            file_put_contents($filename,
                $twig->render(
                    'config.xml.twig',
                    array(
                        'name' => $name,
                        'display_name' => $display_name,
                        'description' => $description,
                        'author' => $author,
                        'tab' => $tab
                    )
                )
            );

            $output->writeln('..copy index.php file');
            $fs->copy(__DIR__.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'module'.DIRECTORY_SEPARATOR.'index.php', $dir.DIRECTORY_SEPARATOR.'index.php');

            $output->writeln('..copy logo.png file');
            $fs->copy(__DIR__.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'module'.DIRECTORY_SEPARATOR.'logo.png', $dir.DIRECTORY_SEPARATOR.'logo.png');

            $output->writeln('process finished!');
        } catch (IOExceptionInterface $e) {
            $output->writeln('An error occurred while creating your module: '.$e->getMessage());
        }


    }
}