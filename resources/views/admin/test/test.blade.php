@extends('admin.layouts.main')
@section('title', '商品列表')
@section('header')
@endsection
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">查询操作区</div>
        <form id="search_form" class="form-inline navbar-form" style="padding: 0" method="get" action="">
            <div class="form-group">
                <label class="sr-only">标题</label>
                <div>
                    <input type="text" name="goods_title" style="max-width: 150px;" value="" class="form-control input-sm" placeholder="">
                </div>
            </div>
            <div class="form-group">
                <label class="sr-only">标题</label>
                <div>
                    <input type="text" name="goods_sn" value="" class="form-control input-sm" placeholder="">
                </div>
            </div>
            <div class="form-group">
                <select class="form-control input-sm" name="cat_id">
                    <option value="">--下拉框--</option>
                    <option value="1" selected>选项1</option>
                    <option value="2" selected>选项2</option>
                    <option value="3" selected>选项3</option>
                    <option value="4" selected>选项4</option>
                </select>
            </div>
            <input class="btn btn-success btn-sm" id="search" type="submit" value="搜索"/>
        </form>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">查询列表区
            <span class="operate pull-right">
            <a class="btn btn-warning btn-sm batch" title="" data-loading-text="批量处理中，请稍后..." autocomplete="off" data-operate="aa1"><span class="glyphicon glyphicon-arrow-up"></span> 批量处理1</a>
            <a class="btn btn-warning btn-sm batch" title="" data-loading-text="批量处理中，请稍后..." autocomplete="off" data-operate="aa2"><span class="glyphicon glyphicon-arrow-up"></span> 批量处理2</a>
        </span>
        </div>
        <table class="table table-striped table-bordered table-hover table-condensed table-responsive">
            <thead>
            <tr>
                <th><input type="checkbox" title="全选" name="check_all"></th>
                <th>编号ID</th>
                <th>字段1</th>
                <th>字段2</th>
                <th>字段3</th>
                <th>字段4</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" name="check" value="1"></td>
                    <td>1</td>
                    <td>aaa</td>
                    <td>cc</td>
                    <td>abb</td>
                    <td><span class="glyphicon glyphicon-ok text-success" data-value="1"  title="是" aria-hidden="true"> <span class="glyphicon glyphicon-remove text-danger" data-value="0"  title="否" aria-hidden="true"></td>
                    <td>
                        <a class="btn btn-info btn-sm edit" data-toggle="modal" data-target="#infoModal"  data-info-id="1" title="编辑弹窗"><span class="glyphicon glyphicon-edit" ></span></a>
                        <a class="btn btn-primary btn-sm"  href="" target="_blank" title=""><span class="glyphicon glyphicon-th"></span></a>
                        <a class="btn btn-warning btn-sm" title="" ><span class="glyphicon glyphicon-refresh"></span></a>
                        <a class="btn btn-danger btn-sm del" data-info-id="1" title="删除"><span class="glyphicon glyphicon-trash"></span></a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="center-block text-center">{!! $pageStr ?? '' !!}</div>
@endsection
@section('other')
    <div class="modal fade" id="infoModal"  role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" >
        <div class="modal-dialog modal-lg" role="document" >
            <div class="modal-content" style="width: 960px;">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="myModalLabel">操作</h4>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>



@endsection

@section('javascript')
    <script type="text/html" id="infoTpl">
        <div class="form-horizontal" >
            {{ csrf_field() }}
            <input type="text" class="hide" name="id" value="@{{ info.id }}">
            <div class="form-group">
                <label class="col-sm-1 control-label input-sm">fieldName1</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control input-sm" name="field1" disabled value="" placeholder="">
                </div>
                <label class="col-sm-1 control-label input-sm">fieldName2<span class="need">*</span></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control input-sm" name="field2" value="" placeholder="">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-1 control-label input-sm">fieldName3</label>
                <div class="col-sm-2">
                    <div class="input-group">
                        <input type="text" class="form-control input-sm" name="field3" value="" placeholder="">
                        <span class="input-group-addon">unit</span>
                    </div>
                </div>
                <label class="col-sm-1 control-label input-sm">fieldName4</label>
                <div class="col-sm-2">
                    <label class="radio-inline"> <input type="radio" name="field4" value="1" checked> 是 </label>
                    <label class="radio-inline"> <input type="radio" name="field4" value="0" > 否 </label>
                </div>
                <label class="col-sm-1 control-label input-sm">fieldName5</label>
                <div class="col-sm-3">
                    <textarea  class="form-control input-sm" name="field5">data</textarea>
                </div>
            </div>
        </div>
    </script>
    <script>
        // 绑定全选 取消操作
        _Admin.checkbox.init('input[name=check_all]','input[name=check]');

        $('#infoModal').on('shown.bs.modal', function (e) {
            $('#infoModal .modal-body').html(template('infoTpl', {info:{}}));
        });
    </script>
@endsection
