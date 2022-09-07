<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Controller;

use Integrated\Bundle\ContentBundle\Document\Content\File;
use Integrated\Bundle\ContentBundle\Provider\ContentProvider;
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends AbstractController
{
    public function __construct(
        private ContentProvider $provider
    )
    {
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //Retrieve all records that are:
        // - A file
        // - A document, word, pdf, whatever
        // - A movie?
        // - Other?
        // What do I need to do to get this in Solr?
        $request->query->set('class', File::class);

        //Why only 1 result?
        $items = $this->provider->getContentFromSolr($request, 100);

//        Test data
        $headers = ['name', "rating", "genre", "creative type", "time"];
        $movies = [
            ["1", "Avatar", "425000000", "Action", "Science Fiction", "162"],
            ["2", "Titanic", "200000000", "Thriller/Suspense", "Historical Fiction", "194"],
            ["3", "Jurassic World", "215000000", "Action", "Science Fiction", "124"],
            ["4", "The Avengers", "225000000", "Adventure", "Super Hero", "143"],
            ["5", "Furious 7", "190000000", "Action", "Contemporary Fiction", "137"],
            ["6", "The Avengers Age of Ultron", "250000000", "Action", "Super Hero", "141"],
            ["7", "Harry Potter and the Deathly Hallows: Part II", "125000000", "Adventure", "Fantasy", "131"],
            ["8", "Frozen", "150000000", "Adventure", "Kids Fiction", "102"],
            ["9", "Iron Man 3", "200000000", "Action", "Super Hero", "130"],
            ["10", "Minions", "74000000", "Comedy", "Kids Fiction", "91"],
            ["11", "The Lord of the Rings: The Return of the King", "94000000", "Adventure", "Fantasy", "201"],
            ["12", "Transformers: Dark of the Moon", "195000000", "Action", "Science Fiction", "154"],
            ["13", "Skyfall", "200000000", "Action", "Contemporary Fiction", "143"],
            ["14", "Transformers: Age of Extinction", "210000000", "Action", "Science Fiction", "165"],
            ["15", "The Dark Knight Rises", "275000000", "Action", "Super Hero", "164"],
            ["16", "Toy Story 3", "200000000", "Adventure", "Kids Fiction", "102"],
            ["17", "Pirates of the Caribbean: Dead Mans Chest", "225000000", "Adventure", "Fantasy", "151"],
            ["18", "Pirates of the Caribbean: On Stranger Tides", "250000000", "Adventure", "Fantasy", "136"],
            ["19", "Jurassic Park", "63000000", "Action", "Science Fiction", "126"],
            ["20", "Star Wars Ep. I: The Phantom Menace", "115000000", "Adventure", "Science Fiction", "133"]
        ];

        return $this->render('@IntegratedContent/media/index.html.twig', [
            'items' => $items,
            'movies' => $movies,
            'headers' => $headers,
        ]);
    }

    /**
     * This function shows 1 media item and gives the user a few actions
     * @param Request $request
     * @return Response
     */

    public function show(Request $request, $id = null)
    {
        return $this->render('@IntegratedContent/media/show.html.twig', [
            'id' => $id
        ]);
    }

    public function edit(Request $request, $id = null)
    {
//        $request->query->set('class', File::class);
//        $items = $this->provider->getContentFromSolr($request, 10);

        return $this->render('@IntegratedContent/media/edit.html.twig', [
            'id' => $id
        ]);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function save(Request $request)
    {
        //get post data from request

        //check ?

        //save
    }
}
