<?php
Route::get("/", ["uses" => "DashboardController@index", "as" => "index"]);

Route::resource("structure", "StructureController", [
    "middleware" => ["can:panel.structure.*"],
]);
Route::post("/structure/{id}/preview", [
    "uses" => "StructureController@preview",
    "as" => "structure.preview",
    "middleware" => ["can:panel.structure.preview"],
]);

Route::resource("website", "WebsiteController", [
    "middleware" => ["can:panel.website.*"],
]);

Route::resource("node", "NodeController", [
    "middleware" => ["can:panel.node.*"],
]);

Route::resource("node.page", "PageController", [
    "middleware" => ["can:panel.page.*"],
    "only" => ["create"],
]);

Route::resource("page", "PageController", [
    "middleware" => ["can:panel.page.*"],
    "except" => ["create"],
]);

Route::post("/file", [
    "uses" => "FileController@upload",
    "as" => "file.upload",
    "middleware" => ["can:panel.file.upload"],
]);

//TODO:fix form
/*Route::resource("form", "FormController", [
    "middleware" => ["can:panel.form.*"],
]);*/
