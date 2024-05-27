<?php
use think\facade\Route;

// 搜索
Route::group('search', function () {
    // 搜索
    Route::group('search', function () {
        // 获取筛选列表
        Route::get('get_filter', 'search.search/getFilter');
        // 获取筛选商品列表
        Route::get('get_product', 'search.search/getProduct');
    });
    // 关键词搜索
    Route::group('search_guess', function () {
        // 获取关键词搜索列表
        Route::get('index', 'search.searchGuess/index');
    });
});
