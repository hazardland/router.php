<?php

    include 'lib/debug/debug.php';
    include './app.php';

    app::$config['path']['view'] = __DIR__.'/';

    app::get('hi/{name}-{last}',function($name,$last){
        echo "hi ".$name.", ".$name." ".$last;
        echo app::url('hi',['name'=>'john','last'=>'doe']);
    })
    ->name('hi');

    app::get('bye',function(){
        app::view('home');
        echo "hi";
    });

    app::run();

//need session
//need cache ?
//need select::options
