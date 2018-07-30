<?php

namespace App\Http\Controllers;

use App\Component\Auth\AuthUser;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

/**
 * Class Controller
 *
 * @property string $userId
 *
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var Request
     */
    protected $request;


    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->request = app('request');
    }

    /**
     * 获取不存在的属性
     *
     * @param string $name
     *
     * @return mixed
     * @throws
     */
    public function __get($name)
    {
        if ($name == 'userId') {
            $value = Auth::guard('common')->id();
        } else {
            throw new \ErrorException('unknown property ' . $name);
        }
        $this->$name = $value;

        return $this->$name;
    }
}
