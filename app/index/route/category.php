<?php
use think\facade\Route;

// 商品分类
Route::group('category', function () {
    // 商品分类
    Route::group('category', function () {
        // 获取当前分类的父级分类
        Route::get('parent_tree', 'category.category/parentTree');
        // 获取所有分类
        Route::get('all', 'category.category/all');
        // 商品相关分类
        Route::get('relate_info', 'category.category/relateInfo');
        // 商品相关分类
        Route::get('relate_category', 'category.category/getRelateCategory');
        // 商品相关品牌
        Route::get('relate_brand', 'category.category/getRelateBrand');
        // 商品相关排行
        Route::get('relate_rank', 'category.category/getRelateRank');
        // 商品相关文章
        Route::get('relate_article', 'category.category/getRelateArticle');
        // 商品相关排行
        Route::get('relate_look_also', 'category.category/getRelateLookAlso');
        // 热门分类
        Route::get('hot', 'category.category/hot');
    });
});
