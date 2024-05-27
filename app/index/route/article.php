<?php
use think\facade\Route;

// 文章
Route::group('article', function () {
    // 文章
    Route::group('article', function () {
        // 文章列表
        Route::get('list', 'article.article/list');
        // 资讯类文章详情
        Route::get('news_info', 'article.article/newsInfo');
        // 帮助类文章详情
        Route::get('issue_info', 'article.article/issueInfo');
    });
    // 文章分类
    Route::group('category', function () {
        // 文章分类
        Route::get('list', 'article.category/list');
        // 首页帮助分类与文章
        Route::get('index_bzzx_list', 'article.category/indexBzzxList');
    });
});