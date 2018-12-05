<?php

    include 'lib/debug/debug.php';
    include './app.php';

    app::get('hi/{name}-{last}',function($name,$last){
        echo "hi ".$name.", ".$name." ".$last;
    });

    app::get('bye',function(){
        echo "bye";
    });

    app::run();
