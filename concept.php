<?php

include './config/config.php';

app::$config['path']['view'] = '/var/www/something.com/public/views';
app::$config['view']['404'] = '404';
app::$config['locale'] = ['en','ge'];

// call anonymous function and pass id
// links with
// 'en/news/13', 'ge/news/13' and 'news/13' will activate route
app::get ('news/{id}',function($id){
    //parse view with passed variables
    app::view('news_item',['id'=>$id]);
})
->name('news') //short key of route
->type('id','int'); //restrict type of id



/*
    call some method of class
*/
app::post ('news/publish','home.index');

app::run();

//get current locale
app::locale();
app::url('news',5);
