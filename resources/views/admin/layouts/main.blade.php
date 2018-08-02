<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <title>通用后台 - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" id="csrf-token">
    <meta name="renderer" content="webkit">
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/messenger/messenger.css') }}"/>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/messenger/messenger-theme-block.css') }}"/>

    <script type="text/javascript" src="{{  URL::asset('admin/assets/js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{  URL::asset('admin/assets/js/jquery.cookie.js') }}"></script>
    <script type="text/javascript" src="{{  URL::asset('admin/assets/bootstrap/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{  URL::asset('admin/assets/js/template.js') }}"></script>
    <script type="text/javascript" src="{{  URL::asset('admin/assets/messenger/messenger.min.js') }}"></script>
    @yield('header')
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/css/admin.css') }}"/>
    <script type="text/javascript" src="{{  URL::asset('admin/assets/js/admin.js') }}"></script>
</head>
<body>
<div class="container-build">
    <div class="col-md-12">
        <nav class="navbar navbar-inverse">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">通用后台</a>
                </div>

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="#">菜单1</a></li>
                        <li><a href="#">菜单2</a></li>
                        <li><a href="#">菜单2</a></li>
                        <li><a href="#">菜单2</a></li>
                        <li><a href="#">菜单2</a></li>
                        <li><a href="#">菜单2</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="javascript:void(0);"><span class="glyphicon glyphicon-user"></span> xzp</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">下拉操作 <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="#">主页</a></li>
                                <li><a href="#">更改密码</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#">退出</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
    <div class="col-md-12">
        <div class="col-md-2">
            <div class="list-group">
                <a class="list-group-item  list-group-item-info text-center disabled">子菜单</a>
                <a href="#" class="list-group-item active">子菜单1</a>
                <a href="#" class="list-group-item">子菜单2</a>
            </div>
            <div class="list-group customMenu">
            </div>
            <div class="panel panel-default">
                <div class="panel-heading text-center">公告</div>
                <div class="panel-body">
                    说明情况 说明情况 说明情况 说明情况 说明情况 说明情况
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading text-center">工具</div>
                <div class="panel-body">
                    <div class="list-group">
                        <a href="javascript:void(0);" onclick="$.removeCookie('perPage');_Admin.tips('操作成功');window.location.reload();" class="list-group-item list-group-item-info text-center">还原默认分页条数</a>
                        <a href="javascript:void(0);" onclick="_Admin.clearForm('#search_form');$('#search_form').submit();" class="list-group-item list-group-item-danger text-center">清除查询区条件数据</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-10">
            <ol class="breadcrumb">
                <li><a href="#">主菜单</a></li>
                <li><a href="#">子菜单</a></li>
                <li class="active">@yield('title')</li>
                <a class="pull-right" href="javascript:void(0);" id="CustomAddBtn"  data-name="子菜单名称" data-url="子菜单URL" title="添加到常用菜单列表" ><span class="glyphicon glyphicon-plus"></span></a>
                <a href="javascript:void(0);" class="pull-right" style="margin-right:50px;"><span class="glyphicon glyphicon-time"></span> <span class="showTime">{{ date('Y-m-d H:i:s') }}</span></a>
            </ol>
            @yield('content')
        </div>
        <div class="col-md-12">
            <p class="center-block text-center">&copy; 2018-300 本人版权所有</p>
            <p class="center-block text-center">Developed by xzp</p>
        </div>
    </div>
    <div>
        @yield('other')
    </div>
    <div>
        @yield('javascript')
    </div>
</div>
<script type="text/javascript">
    $(function(){
        _Admin.customMenus.init('xzp', '.customMenu', '#CustomAddBtn');
        _Admin.showTime('.showTime');
    });
</script>
</body>
</html>
