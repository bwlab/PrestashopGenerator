PrestashopGenerator
===================

A simple generator module for Prestashop

<iframe width="640" height="480" src="//www.youtube.com/embed/E6gmHFSGYxk" frameborder="0" allowfullscreen></iframe>

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
    
