<?php

    app::config ('path.view', __DIR__);
    app::config ('view.404', '404');
    app::config ('locale', ['en','ge']);

    app::get ('news/{id}', function($id){
        app::view ('news_item',['id'=>$id]);
        app::url ('news', ['id'=>5]); //create url
        app::url(); //current url
    })
    ->filter('filter_things')
    ->before('check_input')
    ->before('things')
    ->after('log_things')
    ->name('news')
    ->type('id','int');

    app::get('person/{last_name}-{first_name}', 'person.page');

    class person
    {
        public function page ($last_name, $first_name)
        {

        }
    }

    app::run();
