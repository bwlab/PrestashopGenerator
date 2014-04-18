<?php

namespace Bwlab\Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Symfony\Component\Finder\Finder;
use Twig_Loader_String;
use Twig_Environment;
use Twig_Loader_Filesystem;

class ControllerModuleCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Add a controller to module')
            ->setHelp('')
            ->setName('module:controller:add');
            
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //tinit
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/templates/controller');
        $twig = new Twig_Environment($loader, array());
        $fs = new Filesystem();
        
        //setup dialog
        $dialog = $this->getHelper('dialog');

        //ask module name
        $name = $dialog->ask($output, '<comment>Existent module name</comment>: ');

        //ask type 
        $type = array('admin', 'front');
        $idxtype = strtolower(
                $dialog->select(
                    $output, 
                    '<comment>Select type</comment>: ',
                    $type
                    )
            );
        $site = $type[$idxtype];

        $controllername = strtolower(
                $dialog->ask(
                        $output,
                        '<comment>Controller name to create</comment>: '
                    )
            );

        $viewname = strtolower(
                $dialog->ask(
                        $output,
                        '<comment>View name to use</comment>: '
                    )
            );

        
        //init dir
        $dir = $this->getBaseDir($name);
        $indexphpfile = __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'index.php';
        $output->writeln("create controller " . $controllername . " in module " . $name);

        //config.xml
        try {
            $finder = new Finder();
            $finder->directories()->in($dir);

            //recupero la cartella view
            if (!count($finder->path('controllers'))) {
                $dir .= DIRECTORY_SEPARATOR . 'controllers';
                $fs->mkdir($dir);
                $fs->copy($indexphpfile, $dir . DIRECTORY_SEPARATOR . 'index.php');
            };

            $d = $finder->directories()->name($site);
            if (!count($d)) {
                $dir .= DIRECTORY_SEPARATOR . $site;
                $fs->mkdir($dir);
                $fs->copy($indexphpfile, $dir . DIRECTORY_SEPARATOR . 'index.php');
            };

            $ctrldir = $this->getBaseDir($name) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $site;

            if (!count($d)) {
                $fs->mkdir($ctrldir);
                $fs->copy($indexphpfile, $ctrldir . DIRECTORY_SEPARATOR . 'index.php');
            }

            $formatter = $this->getHelperSet()->get('formatter');

            switch ($site) {
                case 'front':
                    $filename = $ctrldir . DIRECTORY_SEPARATOR . $controllername . '.php';
                    $fs->touch($filename);
                    file_put_contents($filename,
                        $twig->render(
                            'frontcontroller.php.twig',
                            array(
                                'name' => $name,
                                'controllername' => $controllername,
                                'viewname' => $viewname,
                            )
                        )
                    );

                    $formattedLine = $formatter->formatSection(
                        'to call controller on front, add code in your template',
                        '  {$link->getModuleLink(\'' . $name . '\', \'' . $controllername . '\', [], true)|escape:\'html\'}'
                    );
                    $output->writeln($formattedLine);
                    break;

                case 'admin':
                    $filename = $ctrldir . DIRECTORY_SEPARATOR . 'Admin' . ucfirst($controllername) . 'Controller.php';
                    $fs->touch($filename);
                    file_put_contents($filename,
                        $twig->render(
                            'admincontroller.php.twig',
                            array(
                                'name' => $name,
                                'controllername' => $controllername,
                                'viewname' => $viewname,
                            )
                        )
                    );

                    break;
            }

        } catch (IOExceptionInterface $e) {
            $output->writeln('An error occurred while creating your module: ' . $e->getMessage());
        }


    }

    /**
     * @param $name
     * @return string
     */
    private function getBaseDir($name)
    {
        $dir = _PS_MODULE_DIR_ . $name;
        return $dir;
    }

}