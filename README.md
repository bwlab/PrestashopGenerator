PrestashopGenerator
===================

A simple generator module for Prestashop

See [video on YouTube][1]

#Install
To install create folder bin in Prestashop Root directory

    mkdir bin
    cd bin

Then clone repo

    git clone https://github.com/BWLab/PrestashopGenerator.git
    chmod u+x console.sh
    cd ..

Now create composer.json

    touch composer.json

and insert 
    
    {
        "require": {
            "twig/twig": "1.*",
            "symfony/console": "2.5.*@dev",
            "symfony/finder": "2.5.*@dev",
            "symfony/filesystem": "2.3.*@dev"
        },
        "autoload": {
        "classmap": ["bin/commands"]
        }
    }

Install dependencies

    composer update
    
#Use generator
##init module
    
    bin/console.sh module:init <name> <module display name> "<dscription>" <author> <tab>
    


  [1]: http://youtu.be/E6gmHFSGYxk