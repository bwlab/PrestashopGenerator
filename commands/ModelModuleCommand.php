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

class ModelModuleCommand extends Command
{

    private $fieldtype;
    private $requiredfield;

    protected function configure()
    {
        $this
            ->setDescription('Add a model to module')
            ->setHelp('')
            ->setName('module:model:add');
        
        $this->initFieldsAttributes();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //tinit
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/templates/model');
        $twig = new Twig_Environment($loader, array());
        $fs = new Filesystem();
        
        //setup dialog
        $dialog = $this->getHelper('dialog');

        //ask module name
        $modulename = $dialog->ask($output, '<comment>Existent module name</comment>: ');


        $modelname = strtolower(
                $dialog->ask(
                        $output,
                        '<comment>Model name to create</comment>: '
                    )
            );

        $tablename = strtolower(
                $dialog->ask(
                        $output,
                        '<comment>Table name linked</comment>: '
                    )
            );

        //creazione lista campi
        $stop = true;
        $fields = array();


        while ($stop) {
            $name = strtolower(
                    $dialog->ask(
                        $output,
                        '<comment>Field name to create</comment>: ',
                        null
                    )
                );
    
            if(!$name){
                $stop = false;
              break;  
            } 
        
            $type = $dialog->select(
                        $output,
                        '<comment>Type of field</comment>: ',
                        $this->fieldtype
                    );

            $required = $dialog->select(
                        $output,
                        '<comment>Is required</comment>: ',
                        $this->requiredfield
                    );
            
            
            $fields[$name]['fieldname'] = $name;
            $fields[$name]['type'] = $this->getPrestashopFieldType($this->fieldtype[$type]);
            $fields[$name]['required'] = $this->requiredfield[$required];

        }
        
        $primarykey = strtolower(
                $dialog->ask(
                        $output,
                        '<comment>Name of field of primary</comment>: '
                    )
            );

        //init dir
        $dir = $this->getBaseDir($modulename);
        $indexphpfile = __DIR__ . DIRECTORY_SEPARATOR . 'templates'. DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'index.php';
        $output->writeln("create model " . $modelname . " in module " . $modulename);

        //config.xml
        try {
            $finder = new Finder();
            $finder->directories()->in($dir);

            //recupero la cartella classe
            if (!count($finder->path('classes'))) {
                $dir .= DIRECTORY_SEPARATOR . 'classes';
                $fs->mkdir($dir);
                $fs->copy($indexphpfile, $dir . DIRECTORY_SEPARATOR . 'index.php');
            };

            $modelfile = $this->getBaseDir($modulename) . DIRECTORY_SEPARATOR . 'classes'.DIRECTORY_SEPARATOR.$modelname.'.php';
            $fs->touch($modelfile);

            file_put_contents($modelfile,
                    $twig->render(
                        'objectmodel.php.twig',
                        array(
                            'modelname' => $modelname,
                            'fields' => $fields,
                            'primarykey' => $primarykey,
                            'tablename' => $tablename,
                        )
                    )
                );

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
    private function initFieldsAttributes(){
        
        $this->fieldtype = array(
                'string'=>'string',
                'integer'=>'integer',
                'bool'=>'bool',
                'float'=>'float',
                'date'=>'date',
                'html'=>'html',               
            );
        $this->requiredfield = array(
                'true'=>'true', 
                'false'=>'false',
            );

    }
    private function getPrestashopFieldType($generatortype){
        switch ($generatortype) {
            case 'string':
                return 'TYPE_STRING';
            break;
            case 'bool':
                return 'TYPE_BOOL';
                break;
            case 'float':
                return 'TYPE_FLOAT';
                break;
            case 'date':
                return 'TYPE_DATE';
                break;
            case 'html':
                return 'TYPE_HTML';
                break;
            case 'integer':

                break;
            default:
                throw new Exception("Error Processing translate field type", 1);
                
                break;
        }
    }
}