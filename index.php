<?php

    include './app.php';

    app::run();
    var_export(app::$config);
    var_export(app::$request);

    function test ($type){

    };

    test (T_INT);
