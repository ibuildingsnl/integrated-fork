# IntegratedSlugBundle #
Provides a slugger which can generate a slug from a string and event listeners to auto-generate slugs on chosen fields

## Operations Runbook

### Purpose
- Generate and normalize slugs from source fields/methods
- Auto-apply slug updates through listeners/mapping metadata

### Commands
This bundle does not expose standalone console commands.

### Cron And Workers
No dedicated cron worker.

### Verification
- Create/update documents with slug mapping and verify slug field output.
- Test multi-field and custom-separator behavior from mapping examples.

### Troubleshooting
- Slug not updating: verify slug mapping metadata on target field and source field/method names.
- Unexpected separator/output: validate mapping options and normalization logic.
- Duplicate collisions: verify higher-level uniqueness constraints in consuming document/storage layer.

## Requirements ##
* See the require section in the composer.json

## Documentation ##
* [Integrated for developers website](http://www.integratedfordevelopers.com "Integrated for developers website")

## Installation ##
This bundle can be installed following these steps:

### Install using composer ###

    $ php composer.phar require integrated/slug-bundle:~0.3

### Enable the bundle ###

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Integrated\Bundle\SlugBundle\IntegratedSlugBundle()
            // ...
        );
    }

## Example
    
    use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
    use Integrated\Bundle\SlugBundle\Mapping\Attributes\Slug;
    
    class Article
    {
        /**
         * @var string
         * @ODM\String
         */
        protected $title;
    
        /**
         * @var string
         * @ODM\String
         */
        #Slug[fields: ['getSlug']]
        protected $slug;
        
        ...
    }

### Multiple fields

    #Slug[fields: ['title', 'anotherField']]
    
### Custom seperator

     #Slug[fields: ['title'], seperator: '_']
    
### Custom method to generate slug
    
    #Slug[fields: ['getSlug']]

## License ##
This bundle is under the MIT license. See the complete license in the bundle:

    LICENSE

## Contributing ##
Pull requests are welcome. Please see our [CONTRIBUTING guide](http://www.integratedfordevelopers.com/contributing "CONTRIBUTING guide").

## About ##
This bundle is part of the Integrated project. You can read more about this project on the
[Integrated for developers](http://www.integratedfordevelopers.com "Integrated for developers") website.
   
