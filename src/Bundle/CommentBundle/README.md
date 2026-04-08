# IntegratedCommentBundle #
This bundle provides block management

## Operations Runbook

### Purpose
- Add inline editor comments to content fields (including TinyMCE)
- Persist/retrieve/delete comment threads and replies
- Provide Twig filter support for comment marker stripping

### Commands
This bundle does not expose standalone console commands.

### Routes
- `/comment/new/{content}/{field}`
- `/comment/delete/{comment}` (`POST`)
- `/comment/delete/{comment}/{replyId}` (`POST`)
- `/comment/{comment}`

### Cron And Workers
No dedicated cron worker.

### Verification
- Add/edit content comments in admin editor.
- Delete comment and reply flows via comment routes.
- Verify `remove_comments` Twig filter strips marker comments from rendered output.

### Troubleshooting
- Comment markers visible in output: apply `|remove_comments` in rendering path where raw content is shown.
- Delete endpoint fails: verify CSRF/method and route wiring in admin UI.
- TinyMCE integration mismatch: ensure editor plugin/event listener assets are loaded.

## Requirements ##
* See the require section in the composer.json

## Features ##
* Ability to add comments to input fields and tinyMCE editor

## Documentation ##
* [Integrated for Developers](http://integratedfordevelopers.com/ "Integrated for Developers")

## Installation ##
This bundle can be installed following these steps:

### Install using composer ###

    $ php composer.phar require integrated/comment-bundle:~0.6

### Enable the bundle ###

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Integrated\Bundle\CommentBundle\IntegratedCommentBundle()
            // ...
        );
    }

### Import the routing ###

    # app/config/routing.yml
    integrated_comment:
        resource: "@IntegratedCommentBundle/Resources/config/routing.xml"
        prefix: "/admin"
        
### Escaping comments ###
Comments made with tinyMCE will be added as html comments inside the source code, like this:

    <p>text <!--integrated-comment=0a80640f3d5e380baab6d8099aad9580-->commented text<!--end-integrated-comment--> not commented text</p>
    
If you don't like the html comments in your source code you can filter it with twig filter "remove_comments"
    
    {{ content.content|remove_comments }}

## License ##
This bundle is under the MIT license. See the complete license in the bundle:

    LICENSE

## Contributing ##
Pull requests are welcome. Please see our [CONTRIBUTING guide](http://integratedfordevelopers.com/contributing "CONTRIBUTING guide").

## About ##
This bundle is part of the Integrated project. You can read more about this project on the
[Integrated for Developers](http://integratedfordevelopers.com/ "Integrated for Developers") website.
