# Besproken met Marijn 14-09-2022:

## Adding a field to Solr to be able to query

All files we want have the following Class structure:

ContentType -> File --> Video (Video extends File, extends ContentType)
ContentType -> File --> Image
ContentType -> File --> File?
ContentType -> File -> [CustomContentType]

So, in order to query for those in solar we`re going to add a field:
class_string and fill that with a string 'ContentType_File_Image"
A multivalue field, So you can search for a part of this string and Solr will return it.

This string must be saved with every file that goes to Solr.
We do that with Solr Extensions.
All these Extensions are run when:
 - A file is saved
 - Via command line: solr:indexer:queue ? Not sure, test it later.

The file itself is given with the $data variable

## Update the query

In ContentProvider we add something so we can query for a class_string when this is asked for:
```
if ($class = $request->query->get('class')) {
    $query
        ->createFilterQuery('class_string')
        ->addTag('class_string');
        //filteren op File is goed genoeg
        //multivalued veld in Solr, daar staan meerdere waarden in
}
```

## Controller

We run ContentProvider with the request object:

## Twig Template

We loop over the result and use
Afbeeldingen: door middel van integrated_image afbeelding tonen (https://www.integratedfordevelopers.com/content/documentation/article/7-4-twig-template-engine)
Video: met integrated_image krijg je een “still” van de video (mits ImageMagik beschikbaar is). Dit is soms wel traag, dus ik adviseer hier met lazy loading te werken en integrated_image niet rechtstreeks te gebruiken. Misschien een videoicoon en dan door middel van een ajax request de still ophalen (de integrated_image wordt dan gebruikt in de json die met ajax wordt opgehaald).
Bestanden: wellicht moeten hier icoontjes per bestandstype voor komen

```
[Monday 10:14 AM] Marijn Otte
vendor/integrated/integrated/src/Bundle/IntegratedBundle/Resources/public Komt naar php bin/console assets:install in:

public/bundles/integratedintegrated
```

# Thougts

Started with copying the channels example. ChannelExtension.php
Check if it is a File:
use Integrated\Common\Content\Document\Storage\FileInterface;
if (!$data instanceof ChannelableInterface) {
    return;
}

This event is fired when a contenttype is stored:
$this->eventDispatcher->dispatch(new ContentTypeEvent($contentType), Events::CONTENT_TYPE_UPDATED);

There is an extension config file, but this is not for Solr?
There is a solr folder, not needed?

config/solr.xml shows the extensions, lets use this.
<service id="integrated_content.solr.string_class.extension" class="Integrated\Bundle\ContentBundle\Solr\Extension\StringClassExtension" public="false">
    <argument type="service" id="integrated_content.resolver" />
    <tag name="integrated_solr.type_extension" />
</service>

For now, lets keep the argument and use the resolver, not sure what it does yet.

Because we changed a config, we need to cache clear.

Now if we make the file invalid by using a unfinished statement,
php bin/console solr:indexer:queue --full gives an error
Good!

Whats the best way to debug this? I would prefer... either a test route to transfer 1 file
Or a logger, or a dump in the console.
Lets try a logger
php bin/console debug:container logger
gives a monolog logger

php bin/console debug:container logg
gives more, options:
monolog.logger.console

Not sure what to use, lets just see if we can get this working:

Ok, I ran the wrong command, this needs to be run, which makes a lot of sense:
php bin/console solr:indexer:queue --full && php bin/console solr:indexer:run --full

Now we see some output in Solr with:
type_class:"Integrated\\Bundle\\ContentBundle\\Document\\Content\\Image"

In ContentProvider we can play with the query now.
it is case sensitive, file gives results, File doesnt
$query->createFilterQuery('class')->addTag('class')->setQuery('type_name: "File"');

We changed string_class to class_string. Integrated does a wildcard on fields ending on string. (*_string?)
And makes these fields indexed and multivalued. Now we can set this field correctly.

After setting this field, this works via adding files, but not yet via the console cmd? Not sure.

We can alter the ContentProvider command now:
if ($class = $request->query->get('class_string')) {
$query
    ->createFilterQuery('class_string')
    ->setQuery('class_string: '.$class);
}

And use this in our controller.
$request->query->set('class_string', 'File');

After we get basic things working, we`ll refine this with a variable from the $request for 'File'
And I`m not sure yet how to handle custom classes.

We have an icon now for all files and videos. Later refine this:
 - stills for videos?
 - per filetype a separate icon

We now can search with the input field, i used the field from the index of content

We now have a pulldown, I set the options in MediaController, later move this to the config?

I added a third value for the File class: NonMedia, so we can search for files only.


