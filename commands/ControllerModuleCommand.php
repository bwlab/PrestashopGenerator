<?php

namespace Bwlab\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->setName('module:controller:add')
            ->setDescription('Add a controller to module')
            ->addOption(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'module name'
            )
            ->addOption(
                'site',
                null,
                InputOption::VALUE_REQUIRED,
                'front or admin'
            )
            ->addOption(
                'controllername',
                null,
                InputOption::VALUE_REQUIRED,
                'controller name'
            )
            ->addOption(
                'viewname',
                null,
                InputOption::VALUE_OPTIONAL,
                'view name'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //twig init
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/templates/controller');
        $twig = new Twig_Environment($loader, array());

        $fs = new Filesystem();
        $name = strtolower($input->getOption('name'));
        $site = strtolower($input->getOption('site'));
        $controllername = strtolower($input->getOption('controllername'));
        $viewname = strtolower($input->getOption('viewname'));

        if ($site == 'front') {
            $output->writeln('For front it\'s necessary add a view');
            exit;
        }

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

            //recupero la cartella view
            if (!count($finder->directories()->contains('views'))) {
                $dir .= DIRECTORY_SEPARATOR . 'views';
                $fs->mkdir($dir);
                $fs->copy($indexphpfile, $dir . DIRECTORY_SEPARATOR . 'index.php');
            };

            $d = $finder->directories()->contains('templates');
            if (!count($d)) {
                $dir .= DIRECTORY_SEPARATOR . 'templates';
                $fs->mkdir($dir);
                $fs->copy($indexphpfile, $dir . DIRECTORY_SEPARATOR . 'index.php');
            };

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

                    $d = $finder->directories()->contains('front');
                    $frontdir = $this->getBaseDir($name) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'front';
                    if (!count($d)) {
                        $fs->mkdir($frontdir);
                        $fs->copy($indexphpfile, $frontdir . DIRECTORY_SEPARATOR . 'index.php');
                    };

                    $fs->touch($frontdir . DIRECTORY_SEPARATOR . $viewname . '.tpl');
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