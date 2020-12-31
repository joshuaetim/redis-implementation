<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

class CacheableArticles
{
    protected $articles;

    public function __construct($article)
    {
        $this->articles = $article;
    }

    public function all()
    {
        $articles = Cache::remember('articles.all', 60*60*24, function () {
            return $this->articles->all();
        });
        return json_decode($articles);
    }
}

class Articles
{
    public function all()
    {
        return App\Models\Article::all()->toJson();
    }
}

Route::get('/', function () {
    $articles = new CacheableArticles(new Articles);
    return $articles->all();
});

Route::get('video/{id}', function($id){
    $downloads = Redis::get("video.{$id}.downloads");
    return view('video', ['downloads' => $downloads]);
});

Route::get('video/{id}/download', function ($id) {
    Redis::incr("video.{$id}.downloads");

    return back();
});

Route::get('articles', function(){

    $articles = Cache::remember('articles.all', 60*60, function () {
                    return App\Models\Article::all()->toJson();
                });
    return json_decode($articles);
    // return Cache::get('articles.all');
});

Route::get('articles/trending', function(){

    $trending = Redis::zrevrange('trending_articles', 0, 2);

    $trending = \App\Models\Article::hydrate(array_map('json_decode', $trending));

    dd($trending);
});

Route::get('articles/{article}', function(\App\Models\Article $article){

    Redis::zincrby('trending_articles', 1, $article->toJson());

    return $article;
});

Route::get('/settings', function(){

    $user2stats = array(
        'mode' => 'night',
        'size' => 11,
    );

    // return Redis::hmset('user.3.settings', $user2stats);
});

Route::get('/user/{id}/settings', function($id){

    return Redis::hgetall("user.{$id}.settings");

});

Route::get('user/{id}/size/{size}', function($id, $size){

    Redis::hset("user.{$id}.settings", 'size', $size);

    return Redis::hgetall("user.{$id}.settings");

});