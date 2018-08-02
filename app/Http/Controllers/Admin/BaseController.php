<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * 后台控制器基类
 *
 * Class BaseController
 *
 * @package App\Http\Controllers\Admin
 */
class BaseController extends Controller
{

    /**
     * 视图渲染
     *
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render($view, $data = [], $mergeData = [])
    {
        return view('admin.' . $view, $data, $mergeData);
    }

    /**
     * 获取列表页中每页的条目数
     */
    protected function getPageSize()
    {
        $perPage = isset($_COOKIE['perPage']) ? $_COOKIE['perPage'] : '';

        return $perPage && is_numeric($perPage) && $perPage <= 1000 ? (int)$perPage : 15;
    }


    public function __get($name) { }
}
