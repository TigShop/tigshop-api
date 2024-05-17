<?php

namespace app\common;

use think\facade\Cache;

class CacheManager
{
    public function clearCacheByTag($tag = 'all')
    {
        if ($tag == 'all') {
            Cache::clear();
        } else {
            Cache::tag($tag)->clear();
        }
    }
}
